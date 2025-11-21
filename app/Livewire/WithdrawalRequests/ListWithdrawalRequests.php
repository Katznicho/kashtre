<?php

namespace App\Livewire\WithdrawalRequests;

use App\Models\WithdrawalRequest;
use App\Models\Business;
use App\Models\WithdrawalRequestApproval;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MoneyTrackingService;
use App\Models\MoneyAccount;
use App\Models\BusinessBalanceHistory;
use App\Models\MoneyTransfer;
use App\Notifications\WithdrawalRequestNotification;

class ListWithdrawalRequests extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = WithdrawalRequest::query()->with('business', 'requester')->latest();

        // If user is not from Kashtre, only show their business requests
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        } else if (Auth::check() && Auth::user()->business_id === 1) {
            // Kashtre users should only see business withdrawals that have completed all 3 business approval steps
            // This means status should be 'business_approved' or later stages
            $query->whereIn('status', [
                'business_approved',
                'kashtre_approved',
                'approved',
                'processing',
                'completed',
                'rejected',
                'failed'
            ]);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Request ID')
                    ->copyable()
                    ->copyMessage('ID copied!')
                    ->searchable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),
                
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('withdrawal_charge')
                    ->label('Charge')
                    ->money('UGX')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Net Amount')
                    ->money('UGX')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('withdrawal_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'express' => 'warning',
                        'regular' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bank_transfer' => 'success',
                        'mobile_money' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record): string => $record->status_color)
                    ->formatStateUsing(fn ($record): string => $record->formatted_status)
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('business_approvals_count')
                    ->label('Business Approvals')
                    ->formatStateUsing(fn ($record): string => "{$record->business_approvals_count}/{$record->required_business_approvals}")
                    ->color(fn($record): string => $record->hasBusinessApproval() ? 'success' : 'warning')
                    ->badge()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('kashtre_approvals_count')
                    ->label('Kashtre Approvals')
                    ->formatStateUsing(fn ($record): string => "{$record->kashtre_approvals_count}/{$record->required_kashtre_approvals}")
                    ->color(fn($record): string => $record->hasKashtreApproval() ? 'success' : 'warning')
                    ->badge()
                    ->hidden(fn() => Auth::check() && Auth::user()->business_id !== 1)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(Business::pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'business_approved' => 'Business Approved',
                        'kashtre_approved' => 'Kashtre Approved',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('withdrawal_type')
                    ->label('Withdrawal Type')
                    ->options([
                        'regular' => 'Regular',
                        'express' => 'Express',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (WithdrawalRequest $record): string => route('withdrawal-requests.show', $record)),
                
                // Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Comment (Optional)')
                            ->placeholder('Add an optional comment...')
                            ->rows(3),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data) {
                        $this->approveRequest($record, $data['comment'] ?? '');
                    })
                    ->visible(function (WithdrawalRequest $record) {
                        $user = Auth::user();
                        
                        // Only show if request is pending approval and user can approve
                        if (!in_array($record->status, ['pending', 'business_approved'])) {
                            return false;
                        }
                        
                        if (!$record->canUserApproveAtCurrentStep($user)) {
                            return false;
                        }
                        
                        // Check if user has already approved
                        $currentStep = $record->getCurrentStepNumber();
                        $currentLevel = $record->getCurrentApprovalLevel();
                        $userHasApproved = \App\Models\WithdrawalRequestApproval::where('withdrawal_request_id', $record->id)
                            ->where('approver_id', $user->id)
                            ->where('approval_step', $currentStep)
                            ->where('approver_level', $currentLevel)
                            ->where('action', 'approved')
                            ->exists();
                        
                        return !$userHasApproved;
                    }),
                
                // Reject Action
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Rejection Reason')
                            ->placeholder('Please provide a reason for rejection...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data) {
                        $this->rejectRequest($record, $data['comment'] ?? '');
                    })
                    ->visible(function (WithdrawalRequest $record) {
                        $user = Auth::user();
                        
                        // Only show if request is pending approval and user can approve
                        if (!in_array($record->status, ['pending', 'business_approved'])) {
                            return false;
                        }
                        
                        if (!$record->canUserApproveAtCurrentStep($user)) {
                            return false;
                        }
                        
                        // Check if user has already acted on this request
                        $userHasActed = \App\Models\WithdrawalRequestApproval::where('withdrawal_request_id', $record->id)
                            ->where('approver_id', $user->id)
                            ->exists();
                        
                        return !$userHasActed;
                    }),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->headerActions([
                // No header actions
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No withdrawal requests found')
            ->emptyStateDescription('Create your first withdrawal request to get started.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function approveRequest(WithdrawalRequest $withdrawalRequest, string $comment = '')
    {
        $user = Auth::user();
        
        // Check if user can approve
        if (!$withdrawalRequest->canUserApproveAtCurrentStep($user)) {
            \Filament\Notifications\Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to approve this request.')
                ->danger()
                ->send();
            return;
        }
        
        // Check if user has already approved
        $currentStep = $withdrawalRequest->getCurrentStepNumber();
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $existingApproval = WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
            ->where('approver_id', $user->id)
            ->where('approval_step', $currentStep)
            ->where('approver_level', $currentLevel)
            ->where('action', 'approved')
            ->first();
        
        if ($existingApproval) {
            \Filament\Notifications\Notification::make()
                ->title('Already Approved')
                ->body('You have already approved this request at the current step.')
                ->warning()
                ->send();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create approval record
            WithdrawalRequestApproval::create([
                'withdrawal_request_id' => $withdrawalRequest->id,
                'approver_id' => $user->id,
                'approver_type' => 'user',
                'approver_level' => $this->getUserApproverLevel($user, $withdrawalRequest),
                'approval_step' => $currentStep,
                'action' => 'approved',
                'comment' => $comment ?: 'Approved via list view',
            ]);
            
            // Update step approval counts using the same logic as controller
            $this->updateStepApprovalCounts($withdrawalRequest, $user);
            
            // Check if current step is complete and move to next
            $movedToNextStep = false;
            if ($withdrawalRequest->hasApprovedCurrentStep()) {
                $oldStatus = $withdrawalRequest->status;
                $withdrawalRequest->moveToNextStep();
                // Refresh after moveToNextStep to get updated status
                $withdrawalRequest->refresh();
                $movedToNextStep = true;
                
                // Notify approvers at next step
                $this->notifyNextStepApprovers($withdrawalRequest);
                
                // If moved from business_approved to approved, notify requester
                if ($withdrawalRequest->status === 'approved') {
                    // Update the description in BusinessBalanceHistory from "Hold" to "Accepted"
                    $businessAccount = \App\Models\MoneyAccount::where('business_id', $withdrawalRequest->business_id)
                        ->where('type', 'business_account')
                        ->first();
                    
                    if ($businessAccount) {
                        \App\Models\BusinessBalanceHistory::where('business_id', $withdrawalRequest->business_id)
                            ->where('money_account_id', $businessAccount->id)
                            ->where('reference_type', \App\Models\WithdrawalRequest::class)
                            ->where('reference_id', $withdrawalRequest->id)
                            ->where('description', 'like', '%Withdrawal Request Hold%')
                            ->update([
                                'description' => "Withdrawal Request Accepted - {$withdrawalRequest->uuid}"
                            ]);
                    }
                    
                    // Verify bank schedule was created
                    $bankSchedule = \App\Models\BankSchedule::where('withdrawal_request_id', $withdrawalRequest->id)->first();
                    if (!$bankSchedule) {
                        // Bank schedule was not created, try to create it again
                        Log::warning('Bank schedule not found after approval, attempting to create', [
                            'withdrawal_request_id' => $withdrawalRequest->id
                        ]);
                        try {
                            $withdrawalRequest->createBankSchedule();
                        } catch (\Exception $e) {
                            Log::error('Failed to create bank schedule after approval', [
                                'withdrawal_request_id' => $withdrawalRequest->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $requester = $withdrawalRequest->requester;
                    if ($requester) {
                        $requester->notify(new WithdrawalRequestNotification(
                            $withdrawalRequest,
                            'fully_approved',
                            "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been fully approved and will be processed."
                        ));
                    }
                } elseif ($oldStatus === 'pending' && $withdrawalRequest->status === 'business_approved') {
                    // Business approval complete, notify requester
                    $requester = $withdrawalRequest->requester;
                    if ($requester) {
                        $requester->notify(new WithdrawalRequestNotification(
                            $withdrawalRequest,
                            'step_completed',
                            "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been approved by business and is now pending Kashtre approval."
                        ));
                    }
                }
            }
            
            // Refresh the model
            $withdrawalRequest->refresh();
            
            DB::commit();
            
            \Filament\Notifications\Notification::make()
                ->title('Request Approved')
                ->body('The withdrawal request has been approved successfully.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving withdrawal request', [
                'error' => $e->getMessage(),
                'withdrawal_request_id' => $withdrawalRequest->id,
                'user_id' => $user->id,
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title('Approval Failed')
                ->body('Failed to approve request: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function rejectRequest(WithdrawalRequest $withdrawalRequest, string $comment = '')
    {
        $user = Auth::user();
        
        // Check if user can approve (same permission as reject)
        if (!$withdrawalRequest->canUserApproveAtCurrentStep($user)) {
            \Filament\Notifications\Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to reject this request.')
                ->danger()
                ->send();
            return;
        }
        
        // Check if user has already acted on this request
        $existingApproval = WithdrawalRequestApproval::where('withdrawal_request_id', $withdrawalRequest->id)
            ->where('approver_id', $user->id)
            ->first();
        
        if ($existingApproval) {
            \Filament\Notifications\Notification::make()
                ->title('Already Acted')
                ->body('You have already acted on this request.')
                ->warning()
                ->send();
            return;
        }
        
        if (empty($comment)) {
            \Filament\Notifications\Notification::make()
                ->title('Rejection Reason Required')
                ->body('Please provide a reason for rejection.')
                ->warning()
                ->send();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create rejection record
            WithdrawalRequestApproval::create([
                'withdrawal_request_id' => $withdrawalRequest->id,
                'approver_id' => $user->id,
                'approver_type' => 'user',
                'approver_level' => $this->getUserApproverLevel($user, $withdrawalRequest),
                'action' => 'rejected',
                'comment' => $comment,
            ]);
            
            // Reject the request
            $withdrawalRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $comment,
                'rejected_at' => now(),
            ]);
            
            // Also reject the related request if it exists
            if ($withdrawalRequest->relatedRequest) {
                $withdrawalRequest->relatedRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Related request rejected',
                    'rejected_at' => now(),
                ]);
            }
            
            // No funds to release - withdrawal requests no longer hold funds when created
            
            // Notify requester that request was rejected
            $requester = $withdrawalRequest->requester;
            if ($requester) {
                $requester->notify(new WithdrawalRequestNotification(
                    $withdrawalRequest,
                    'rejected',
                                "Your withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX has been rejected. Reason: {$comment}"
                ));
            }
            
            DB::commit();
            
            \Filament\Notifications\Notification::make()
                ->title('Request Rejected')
                ->body('The withdrawal request has been rejected and funds have been returned to the business account.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting withdrawal request', [
                'error' => $e->getMessage(),
                'withdrawal_request_id' => $withdrawalRequest->id,
                'user_id' => $user->id,
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title('Rejection Failed')
                ->body('Failed to reject request: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function getUserApproverLevel($user, $withdrawalRequest)
    {
        if ($user->business_id == 1) {
            return 'kashtre';
        } else {
            return 'business';
        }
    }
    
    private function updateStepApprovalCounts($withdrawalRequest, $user)
    {
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $currentStep = $withdrawalRequest->getCurrentStepNumber();

        if ($currentLevel === 'business') {
            $stepField = "business_step_{$currentStep}_approvals";
            $withdrawalRequest->increment($stepField);
        } elseif ($currentLevel === 'kashtre') {
            $stepField = "kashtre_step_{$currentStep}_approvals";
            $withdrawalRequest->increment($stepField);
        }

        // Also update the general approval counts
        if ($currentLevel === 'business') {
            $withdrawalRequest->increment('business_approvals_count');
        } elseif ($currentLevel === 'kashtre') {
            $withdrawalRequest->increment('kashtre_approvals_count');
        }
    }
    
    private function notifyNextStepApprovers(WithdrawalRequest $withdrawalRequest)
    {
        $currentLevel = $withdrawalRequest->getCurrentApprovalLevel();
        $currentStep = $withdrawalRequest->getCurrentStepNumber();
        
        if ($currentLevel === 'business' && $currentStep <= 3) {
            // Notify business approvers for next step
            $withdrawalSetting = \App\Models\WithdrawalSetting::where('business_id', $withdrawalRequest->business_id)
                ->where('is_active', true)
                ->first();
            
            if ($withdrawalSetting) {
                $stepApprovalLevel = $withdrawalRequest->getStepApprovalLevel($currentStep);
                $approvers = $withdrawalSetting->allBusinessApprovers()
                    ->where('approval_level', $stepApprovalLevel)
                    ->get();
                
                foreach ($approvers as $approverSetting) {
                    if ($approverSetting->approver_type === 'user') {
                        $approver = \App\Models\User::find($approverSetting->approver_id);
                        if ($approver) {
                            $approver->notify(new WithdrawalRequestNotification(
                                $withdrawalRequest,
                                'pending_approval',
                                "A withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX from {$withdrawalRequest->business->name} requires your approval at Business {$stepApprovalLevel} level."
                            ));
                        }
                    }
                }
            }
        } elseif ($currentLevel === 'kashtre' && $currentStep <= 3) {
            // Notify Kashtre approvers for next step
            $withdrawalSetting = \App\Models\WithdrawalSetting::where('business_id', $withdrawalRequest->business_id)
                ->where('is_active', true)
                ->first();
            
            if ($withdrawalSetting) {
                $stepApprovalLevel = $withdrawalRequest->getStepApprovalLevel($currentStep);
                $approvers = $withdrawalSetting->allKashtreApprovers()
                    ->where('approval_level', $stepApprovalLevel)
                    ->get();
                
                foreach ($approvers as $approverSetting) {
                    if ($approverSetting->approver_type === 'user') {
                        $approver = \App\Models\User::find($approverSetting->approver_id);
                        if ($approver) {
                            $approver->notify(new WithdrawalRequestNotification(
                                $withdrawalRequest,
                                'pending_approval',
                                "A withdrawal request for " . number_format($withdrawalRequest->amount, 2) . " UGX from {$withdrawalRequest->business->name} requires your approval at Kashtre {$stepApprovalLevel} level."
                            ));
                        }
                    }
                }
            }
        }
    }
    
    public function render(): View
    {
        return view('livewire.withdrawal-requests.list-withdrawal-requests');
    }
}
