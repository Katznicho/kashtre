<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\CreditNoteApproval;
use App\Models\CreditNoteWorkflow;
use App\Models\ServiceDeliveryQueue;
use App\Models\ServicePointSupervisor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreditNoteService
{
    public function __construct(
        private readonly MoneyTrackingService $moneyTrackingService,
    ) {
    }

    /**
     * Initiate the refund workflow for a service delivery queue item.
     */
    public function initiateRefundWorkflow(ServiceDeliveryQueue $queue, User $initiator = null, array $context = []): ?CreditNote
    {
        $initiatorId = $initiator?->id;

        Log::info('=== CREDIT NOTE WORKFLOW: INITIATION REQUEST ===', [
            'queue_id' => $queue->id,
            'queue_status' => $queue->status,
            'invoice_id' => $queue->invoice_id,
            'client_id' => $queue->client_id,
            'business_id' => $queue->business_id,
            'service_point_id' => $queue->service_point_id,
            'initiated_by' => $initiatorId,
            'context' => $context,
        ]);

        $workflow = CreditNoteWorkflow::with(['authorizers', 'approvers'])
            ->where('business_id', $queue->business_id)
            ->where('is_active', true)
            ->first();

        $existing = $queue->creditNote()
            ->whereNotIn('status', ['processed', 'cancelled'])
            ->first();

        if ($existing && $workflow) {
            Log::info('Credit note already exists for queue item. Ensuring approvals are in sync.', [
                'queue_id' => $queue->id,
                'credit_note_id' => $existing->id,
                'credit_note_status' => $existing->status,
            ]);

            $sequence = (int) ($existing->approvals()->max('sequence') ?? 0);

            $supervisorIds = $this->resolveSupervisors($workflow, $queue);
            $authorizerIds = $this->resolveAuthorizers($workflow);
            $approverIds = $this->resolveApprovers($workflow);

            $this->ensureStageApprovals($existing, CreditNote::STAGE_SUPERVISOR, $supervisorIds, $sequence);
            $this->ensureStageApprovals($existing, CreditNote::STAGE_AUTHORIZER, $authorizerIds, $sequence);
            $this->ensureStageApprovals($existing, CreditNote::STAGE_APPROVER, $approverIds, $sequence);

            $this->syncCurrentStage($existing);

            return $existing->fresh(['approvals']);
        }

        if (! $workflow) {
            if ($existing) {
                Log::warning('Credit note exists but no active workflow is configured. Returning existing note without sync.', [
                    'credit_note_id' => $existing->id,
                    'business_id' => $queue->business_id,
                ]);

                return $existing;
            }

            Log::warning('No credit note workflow configured for business. Refund workflow aborted.', [
                'business_id' => $queue->business_id,
                'queue_id' => $queue->id,
                'billable_amount' => $queue->price * $queue->quantity,
            ]);

            return null;
        }

        $amount = (float) ($queue->price * $queue->quantity);

        $creditNote = CreditNote::create([
            'service_delivery_queue_id' => $queue->id,
            'invoice_id' => $queue->invoice_id,
            'client_id' => $queue->client_id,
            'business_id' => $queue->business_id,
            'branch_id' => $queue->branch_id,
            'amount' => $amount,
            'reason' => $context['reason'] ?? 'Service item marked as not done',
            'status' => 'pending',
            'current_stage' => null,
            'initiated_by' => $initiatorId,
        ]);

        Log::info('Credit note created', [
            'credit_note_id' => $creditNote->id,
            'credit_note_number' => $creditNote->credit_note_number,
            'amount' => $amount,
            'initiated_by' => $initiatorId,
            'client_id' => $queue->client_id,
        ]);

        $sequence = 0;

        $supervisorIds = $this->resolveSupervisors($workflow, $queue);
        if ($supervisorIds->isEmpty()) {
            Log::warning('No supervisors resolved for credit note workflow. Skipping supervisor stage.', [
                'credit_note_id' => $creditNote->id,
                'service_point_id' => $queue->service_point_id,
            ]);
        }
        $this->ensureStageApprovals($creditNote, CreditNote::STAGE_SUPERVISOR, $supervisorIds, $sequence);

        $authorizerIds = $this->resolveAuthorizers($workflow);
        if ($authorizerIds->isEmpty()) {
            Log::warning('No authorizers configured for credit note workflow.', [
                'credit_note_id' => $creditNote->id,
                'business_id' => $queue->business_id,
            ]);
        }
        $this->ensureStageApprovals($creditNote, CreditNote::STAGE_AUTHORIZER, $authorizerIds, $sequence);

        $approverIds = $this->resolveApprovers($workflow);
        if ($approverIds->isEmpty()) {
            Log::warning('No final approvers configured for credit note workflow.', [
                'credit_note_id' => $creditNote->id,
                'business_id' => $queue->business_id,
            ]);
        }
        $this->ensureStageApprovals($creditNote, CreditNote::STAGE_APPROVER, $approverIds, $sequence);

        Log::info('Credit note workflow approvals generated', [
            'credit_note_id' => $creditNote->id,
            'approval_count' => $creditNote->approvals()->count(),
        ]);

        $this->syncCurrentStage($creditNote);

        return $creditNote->fresh(['approvals']);
    }

    /**
     * Approve an individual credit note approval.
     */
    public function approve(CreditNoteApproval $approval, User $user, ?string $notes = null): CreditNote
    {
        return DB::transaction(function () use ($approval, $user, $notes) {
            $approval->refresh();

            if ($approval->status !== 'pending') {
                throw new \RuntimeException('This approval has already been actioned.');
            }

            if ($approval->assigned_user_id && (int) $approval->assigned_user_id !== (int) $user->id) {
                throw new \RuntimeException('You are not assigned to this approval.');
            }

            $creditNote = $approval->creditNote()->lockForUpdate()->with(['approvals', 'client'])->first();

            if ($creditNote->current_stage !== $approval->stage) {
                Log::warning('Attempt to approve credit note at mismatched stage', [
                    'approval_id' => $approval->id,
                    'credit_note_id' => $creditNote->id,
                    'current_stage' => $creditNote->current_stage,
                    'approval_stage' => $approval->stage,
                    'user_id' => $user->id,
                ]);

                throw new \RuntimeException('This credit note is not awaiting action at your stage.');
            }

            $approval->update([
                'status' => 'approved',
                'acted_by' => $user->id,
                'acted_at' => now(),
                'notes' => $notes,
            ]);

            $otherPending = $creditNote->approvals()
                ->where('stage', $approval->stage)
                ->where('status', 'pending')
                ->where('id', '!=', $approval->id)
                ->get();

            foreach ($otherPending as $pendingApproval) {
                $pendingApproval->update([
                    'status' => 'approved',
                    'acted_by' => $user->id,
                    'acted_at' => now(),
                    'notes' => trim(($pendingApproval->notes ? $pendingApproval->notes . ' ' : '') . '[Auto-closed after stage approval]'),
                ]);
            }

            if ($otherPending->isNotEmpty()) {
                Log::info('Auto-closed additional approvals for stage', [
                    'credit_note_id' => $creditNote->id,
                    'stage' => $approval->stage,
                    'auto_closed_count' => $otherPending->count(),
                ]);
            }

            $creditNote->load('approvals');

            Log::info('Credit note approval recorded', [
                'approval_id' => $approval->id,
                'credit_note_id' => $creditNote->id,
                'stage' => $approval->stage,
                'acted_by' => $user->id,
                'notes_present' => filled($notes),
            ]);

            $pendingRemaining = $creditNote->approvals
                ->where('stage', $approval->stage)
                ->where('status', 'pending')
                ->count();

            Log::info('Credit note stage pending check after approval', [
                'credit_note_id' => $creditNote->id,
                'stage' => $approval->stage,
                'pending_remaining' => $pendingRemaining,
                'total_stage_approvals' => $creditNote->approvals->where('stage', $approval->stage)->count(),
            ]);

            if (! $creditNote->hasPendingApprovalsForStage($approval->stage)) {
                Log::info('All approvals for stage cleared, syncing current stage', [
                    'credit_note_id' => $creditNote->id,
                    'cleared_stage' => $approval->stage,
                ]);

                $this->syncCurrentStage($creditNote, $user);
            } else {
                Log::info('Stage still has pending approvals, no stage sync', [
                    'credit_note_id' => $creditNote->id,
                    'stage' => $approval->stage,
                    'pending_remaining' => $pendingRemaining,
                ]);
            }

            return $creditNote;
        });
    }

    /**
     * Reject an approval and cancel the credit note.
     */
    public function reject(CreditNoteApproval $approval, User $user, ?string $notes = null): CreditNote
    {
        return DB::transaction(function () use ($approval, $user, $notes) {
            $approval->refresh();

            if ($approval->status !== 'pending') {
                throw new \RuntimeException('This approval has already been actioned.');
            }

            if ($approval->assigned_user_id && (int) $approval->assigned_user_id !== (int) $user->id) {
                throw new \RuntimeException('You are not assigned to this approval.');
            }

            $creditNote = $approval->creditNote()->lockForUpdate()->with('approvals')->first();

            $approval->update([
                'status' => 'rejected',
                'acted_by' => $user->id,
                'acted_at' => now(),
                'notes' => $notes,
            ]);

            $creditNote->markCancelled($user, $notes);

            $creditNote->approvals()
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'acted_by' => $user->id,
                    'acted_at' => now(),
                    'notes' => $notes,
                ]);

            Log::info('Credit note rejected', [
                'credit_note_id' => $creditNote->id,
                'acted_by' => $user->id,
                'notes' => $notes,
            ]);

            return $creditNote;
        });
    }

    private function resolveSupervisors(CreditNoteWorkflow $workflow, ServiceDeliveryQueue $queue): Collection
    {
        $assigned = ServicePointSupervisor::where('service_point_id', $queue->service_point_id)
            ->pluck('supervisor_user_id')
            ->filter();

        if ($assigned->isNotEmpty()) {
            $supervisorId = $assigned->first();

            return $supervisorId ? collect([$supervisorId]) : collect();
        }

        if ($workflow->default_supervisor_user_id) {
            return collect([$workflow->default_supervisor_user_id]);
        }

        return collect();
    }

    private function resolveAuthorizers(CreditNoteWorkflow $workflow): Collection
    {
        $authorizers = $workflow->authorizers()->pluck('users.id');

        if ($authorizers->isNotEmpty()) {
            return $authorizers->filter()->values();
        }

        return $workflow->finance_user_id
            ? collect([$workflow->finance_user_id])
            : collect();
    }

    private function resolveApprovers(CreditNoteWorkflow $workflow): Collection
    {
        $approvers = $workflow->approvers()->pluck('users.id');

        if ($approvers->isNotEmpty()) {
            return $approvers->filter()->values();
        }

        return $workflow->ceo_user_id
            ? collect([$workflow->ceo_user_id])
            : collect();
    }

    private function createApproval(CreditNote $creditNote, string $stage, int $sequence, ?int $assignedUserId = null): CreditNoteApproval
    {
        $approval = $creditNote->approvals()->create([
            'stage' => $stage,
            'sequence' => $sequence,
            'assigned_user_id' => $assignedUserId,
            'status' => 'pending',
        ]);

        Log::info('Credit note approval record created', [
            'credit_note_id' => $creditNote->id,
            'approval_id' => $approval->id,
            'stage' => $stage,
            'sequence' => $sequence,
            'assigned_user_id' => $assignedUserId,
        ]);

        return $approval;
    }

    private function ensureStageApprovals(CreditNote $creditNote, string $stage, Collection $userIds, int &$sequence): void
    {
        $existingApprovals = $creditNote->approvals()
            ->where('stage', $stage)
            ->get();

        $stageHasPending = $existingApprovals->where('status', 'pending')->isNotEmpty();
        $stageCompleted = ! $stageHasPending && $existingApprovals->where('status', 'approved')->isNotEmpty();

        if ($stageCompleted) {
            return;
        }

        if ($userIds->isEmpty()) {
            if ($existingApprovals->isEmpty()) {
                $this->createApproval($creditNote, $stage, ++$sequence, null);
            }

            return;
        }

        $existingByUser = $existingApprovals
            ->filter(fn (CreditNoteApproval $approval) => $approval->assigned_user_id !== null)
            ->keyBy('assigned_user_id');

        foreach ($userIds as $userId) {
            if (! $existingByUser->has($userId)) {
                $this->createApproval($creditNote, $stage, ++$sequence, $userId);
            }
        }
    }

    private function syncCurrentStage(CreditNote $creditNote, ?User $actingUser = null): void
    {
        $creditNote->load('approvals');

        Log::info('Syncing credit note current stage', [
            'credit_note_id' => $creditNote->id,
            'current_stage_before' => $creditNote->current_stage,
            'status_before' => $creditNote->status,
            'pending_counts' => $creditNote->approvals
                ->groupBy('stage')
                ->map(fn ($group) => [
                    'pending' => $group->where('status', 'pending')->count(),
                    'approved' => $group->where('status', 'approved')->count(),
                    'rejected' => $group->where('status', 'rejected')->count(),
                ])
                ->toArray(),
        ]);

        foreach (CreditNote::stageOrder() as $stage) {
            if ($creditNote->approvals
                ->where('stage', $stage)
                ->where('status', 'pending')
                ->isNotEmpty()) {
                $creditNote->forceFill(['current_stage' => $stage])->save();

                Log::info('Credit note stage updated', [
                    'credit_note_id' => $creditNote->id,
                    'current_stage' => $stage,
                    'status' => $creditNote->status,
                ]);

                return;
            }
        }

        $creditNote->forceFill([
            'current_stage' => 'approved',
            'status' => 'approved',
        ])->save();

        Log::info('Credit note approvals complete awaiting refund', [
            'credit_note_id' => $creditNote->id,
            'acting_user_id' => $actingUser?->id,
        ]);

        if ($actingUser) {
            DB::afterCommit(function () use ($creditNote, $actingUser) {
                $this->finalizeCreditNote($creditNote->fresh(), $actingUser);
            });
        }
    }

    private function finalizeCreditNote(?CreditNote $creditNote, ?User $actingUser = null): void
    {
        if (! $creditNote) {
            return;
        }

        $creditNote->loadMissing(['client']);

        if (! $creditNote->client) {
            Log::error('Cannot finalize credit note without client', [
                'credit_note_id' => $creditNote->id,
            ]);

            return;
        }

        try {
            $this->moneyTrackingService->processRefund(
                $creditNote->client,
                $creditNote->amount,
                "Credit Note {$creditNote->credit_note_number}",
                $actingUser?->id,
                $creditNote
            );

            $creditNote->forceFill([
                'status' => 'processed',
                'current_stage' => 'refunded',
                'processed_by' => $actingUser?->id,
                'processed_at' => now(),
            ])->save();

            Log::info('Credit note refund processed successfully', [
                'credit_note_id' => $creditNote->id,
                'client_id' => $creditNote->client_id,
                'amount' => $creditNote->amount,
                'processed_by' => $actingUser?->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to process credit note refund', [
                'credit_note_id' => $creditNote->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

