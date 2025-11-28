<?php

namespace App\Http\Controllers;

use App\Models\CreditLimitChangeRequest;
use App\Models\CreditLimitChangeRequestApproval;
use App\Models\CreditLimitApprovalApprover;
use App\Models\Client;
use App\Models\ThirdPartyPayer;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditLimitChangeRequestController extends Controller
{
    /**
     * Display a listing of credit limit change requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        $query = CreditLimitChangeRequest::with([
            'business',
            'initiatedBy',
            'approvals.approver'
        ])
        ->where('business_id', $user->business_id)
        ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by entity type
        if ($request->has('entity_type') && $request->entity_type !== 'all') {
            $query->where('entity_type', $request->entity_type);
        }

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search by entity name (client or third-party payer name)
                $q->whereHasMorph('entity', [Client::class, ThirdPartyPayer::class], function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('phone_number', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('client_id', 'like', "%{$search}%");
                })
                // Search by UUID
                ->orWhere('uuid', 'like', "%{$search}%")
                // Search by reason
                ->orWhere('reason', 'like', "%{$search}%")
                // Search by initiated by user name
                ->orWhereHas('initiatedBy', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                // Search by current or requested credit limit
                ->orWhere('current_credit_limit', 'like', "%{$search}%")
                ->orWhere('requested_credit_limit', 'like', "%{$search}%");
            });
        }

        // Filter by user role
        $userApproverLevels = CreditLimitApprovalApprover::where('business_id', $user->business_id)
            ->where('approver_id', $user->id)
            ->pluck('approval_level')
            ->toArray();

        // If user is an authorizer, show requests waiting for authorization
        if (in_array('authorizer', $userApproverLevels)) {
            $pendingForAuthorization = $query->clone()
                ->where('status', 'initiated')
                ->where('current_step', 2)
                ->whereHas('approvals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id)
                      ->where('approval_level', 'authorizer')
                      ->whereNull('action');
                })
                ->get()
                ->pluck('id');
        }

        // If user is an approver, show requests waiting for approval
        if (in_array('approver', $userApproverLevels)) {
            $pendingForApproval = $query->clone()
                ->where('status', 'authorized')
                ->where('current_step', 3)
                ->whereHas('approvals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id)
                      ->where('approval_level', 'approver')
                      ->whereNull('action');
                })
                ->get()
                ->pluck('id');
        }

        $requests = $query->paginate(20);

        // Get counts for tabs
        $counts = [
            'all' => CreditLimitChangeRequest::where('business_id', $user->business_id)->count(),
            'pending' => CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'pending')->count(),
            'initiated' => CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'initiated')->count(),
            'authorized' => CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'authorized')->count(),
            'approved' => CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'approved')->count(),
            'rejected' => CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'rejected')->count(),
        ];

        // Get user's pending requests
        $myPendingRequests = [];
        if (in_array('authorizer', $userApproverLevels)) {
            $myPendingRequests['authorizations'] = CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'initiated')
                ->where('current_step', 2)
                ->whereHas('approvals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id)
                      ->where('approval_level', 'authorizer')
                      ->whereNull('action');
                })
                ->count();
        }

        if (in_array('approver', $userApproverLevels)) {
            $myPendingRequests['approvals'] = CreditLimitChangeRequest::where('business_id', $user->business_id)
                ->where('status', 'authorized')
                ->where('current_step', 3)
                ->whereHas('approvals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id)
                      ->where('approval_level', 'approver')
                      ->whereNull('action');
                })
                ->count();
        }

        return view('credit-limit-requests.index', compact('requests', 'counts', 'myPendingRequests', 'userApproverLevels'));
    }

    /**
     * Show the form for creating a new credit limit change request
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        // Check if user is an initiator
        $isInitiator = CreditLimitApprovalApprover::where('business_id', $user->business_id)
            ->where('approver_id', $user->id)
            ->where('approval_level', 'initiator')
            ->exists();

        if (!$isInitiator) {
            return redirect()->route('credit-limit-requests.index')
                ->with('error', 'You are not authorized to initiate credit limit change requests.');
        }

        $entityType = $request->get('entity_type', 'client');
        $entityId = $request->get('entity_id');

        $entity = null;
        $currentCreditLimit = 0;

        if ($entityType === 'client' && $entityId) {
            $entity = Client::where('business_id', $user->business_id)->findOrFail($entityId);
            $currentCreditLimit = $entity->max_credit ?? 0;
        } elseif ($entityType === 'third_party_payer' && $entityId) {
            $entity = ThirdPartyPayer::where('business_id', $user->business_id)->findOrFail($entityId);
            $currentCreditLimit = $entity->credit_limit ?? 0;
        }

        return view('credit-limit-requests.create', compact('entity', 'entityType', 'entityId', 'currentCreditLimit'));
    }

    /**
     * Store a newly created credit limit change request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        // Check if user is an initiator
        $isInitiator = CreditLimitApprovalApprover::where('business_id', $user->business_id)
            ->where('approver_id', $user->id)
            ->where('approval_level', 'initiator')
            ->exists();

        if (!$isInitiator) {
            return redirect()->route('credit-limit-requests.index')
                ->with('error', 'You are not authorized to initiate credit limit change requests.');
        }

        $validated = $request->validate([
            'entity_type' => 'required|in:client,third_party_payer',
            'entity_id' => 'required|integer',
            'current_credit_limit' => 'required|numeric|min:0',
            'requested_credit_limit' => 'required|numeric|min:0|gt:current_credit_limit',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Verify entity exists and belongs to business
        if ($validated['entity_type'] === 'client') {
            $entity = Client::where('business_id', $user->business_id)->findOrFail($validated['entity_id']);
            $currentLimit = $entity->max_credit ?? 0;
        } else {
            $entity = ThirdPartyPayer::where('business_id', $user->business_id)->findOrFail($validated['entity_id']);
            $currentLimit = $entity->credit_limit ?? 0;
        }

        // Verify current limit matches
        if (abs($currentLimit - $validated['current_credit_limit']) > 0.01) {
            return back()->withInput()->with('error', 'Current credit limit does not match. Please refresh and try again.');
        }

        DB::beginTransaction();
        try {
            // Create the request
            $creditRequest = CreditLimitChangeRequest::create([
                'business_id' => $user->business_id,
                'initiated_by' => $user->id,
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'current_credit_limit' => $validated['current_credit_limit'],
                'requested_credit_limit' => $validated['requested_credit_limit'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'initiated',
                'current_step' => 2, // Next step is authorization
                'initiated_by_user_id' => $user->id,
                'initiated_at' => now(),
            ]);

            // Get all authorizers and approvers for this business (snapshot at creation time)
            $authorizers = CreditLimitApprovalApprover::where('business_id', $user->business_id)
                ->where('approval_level', 'authorizer')
                ->get();

            $approvers = CreditLimitApprovalApprover::where('business_id', $user->business_id)
                ->where('approval_level', 'approver')
                ->get();

            // Create approval records for all authorizers
            foreach ($authorizers as $authorizer) {
                CreditLimitChangeRequestApproval::create([
                    'credit_limit_change_request_id' => $creditRequest->id,
                    'approver_id' => $authorizer->approver_id,
                    'approval_level' => 'authorizer',
                    'action' => null, // Pending
                    'comment' => null,
                ]);
            }

            // Create approval records for all approvers
            foreach ($approvers as $approver) {
                CreditLimitChangeRequestApproval::create([
                    'credit_limit_change_request_id' => $creditRequest->id,
                    'approver_id' => $approver->approver_id,
                    'approval_level' => 'approver',
                    'action' => null, // Pending
                    'comment' => null,
                ]);
            }

            DB::commit();

            Log::info('Credit limit change request created', [
                'request_id' => $creditRequest->id,
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'initiated_by' => $user->id,
            ]);

            return redirect()->route('credit-limit-requests.show', $creditRequest)
                ->with('success', 'Credit limit change request created successfully. Waiting for authorization.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating credit limit change request', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->withInput()->with('error', 'Failed to create credit limit change request. Please try again.');
        }
    }

    /**
     * Display the specified credit limit change request
     */
    public function show(CreditLimitChangeRequest $creditLimitChangeRequest)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        // Verify business access
        if ($creditLimitChangeRequest->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access.');
        }

        // Load relationships
        $creditLimitChangeRequest->load([
            'business',
            'initiatedBy',
            'approvals.approver',
            'authorizedByUser',
            'approvedByUser',
            'rejectedByUser',
        ]);

        // Check if user can approve/authorize this request
        $userApproval = $creditLimitChangeRequest->approvals()
            ->where('approver_id', $user->id)
            ->whereNull('action')
            ->first();

        $canApprove = false;
        $approvalLevel = null;

        if ($userApproval) {
            if ($userApproval->approval_level === 'authorizer' && $creditLimitChangeRequest->canBeAuthorized()) {
                $canApprove = true;
                $approvalLevel = 'authorizer';
            } elseif ($userApproval->approval_level === 'approver' && $creditLimitChangeRequest->canBeApproved()) {
                $canApprove = true;
                $approvalLevel = 'approver';
            }
        }

        return view('credit-limit-requests.show', compact('creditLimitChangeRequest', 'canApprove', 'approvalLevel', 'userApproval'));
    }

    /**
     * Approve a credit limit change request
     */
    public function approve(Request $request, CreditLimitChangeRequest $creditLimitChangeRequest)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        // Verify business access
        if ($creditLimitChangeRequest->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        // Find user's approval record
        $userApproval = $creditLimitChangeRequest->approvals()
            ->where('approver_id', $user->id)
            ->whereNull('action')
            ->first();

        if (!$userApproval) {
            return back()->with('error', 'You are not authorized to approve this request.');
        }

        DB::beginTransaction();
        try {
            // Update the approval record
            $userApproval->update([
                'action' => 'approved',
                'comment' => $validated['comment'] ?? null,
            ]);

            // Check if this is an authorizer approval
            if ($userApproval->approval_level === 'authorizer') {
                if (!$creditLimitChangeRequest->canBeAuthorized()) {
                    DB::rollBack();
                    return back()->with('error', 'This request cannot be authorized at this time.');
                }

                // Check if all authorizers have approved
                if ($creditLimitChangeRequest->hasAllAuthorizersApproved()) {
                    $creditLimitChangeRequest->update([
                        'status' => 'authorized',
                        'current_step' => 3,
                        'authorized_by_user_id' => $user->id,
                        'authorized_at' => now(),
                    ]);
                }
            }
            // Check if this is an approver approval
            elseif ($userApproval->approval_level === 'approver') {
                if (!$creditLimitChangeRequest->canBeApproved()) {
                    DB::rollBack();
                    return back()->with('error', 'This request cannot be approved at this time.');
                }

                // Check if all approvers have approved
                if ($creditLimitChangeRequest->hasAllApproversApproved()) {
                    // Update the request status
                    $creditLimitChangeRequest->update([
                        'status' => 'approved',
                        'approved_by_user_id' => $user->id,
                        'approved_at' => now(),
                    ]);

                    // Update the actual credit limit
                    if ($creditLimitChangeRequest->entity_type === 'client') {
                        $client = Client::find($creditLimitChangeRequest->entity_id);
                        if ($client) {
                            $client->update([
                                'max_credit' => $creditLimitChangeRequest->requested_credit_limit,
                            ]);
                        }
                    } elseif ($creditLimitChangeRequest->entity_type === 'third_party_payer') {
                        $payer = ThirdPartyPayer::find($creditLimitChangeRequest->entity_id);
                        if ($payer) {
                            $payer->update([
                                'credit_limit' => $creditLimitChangeRequest->requested_credit_limit,
                            ]);
                        }
                    }

                    Log::info('Credit limit updated after approval', [
                        'request_id' => $creditLimitChangeRequest->id,
                        'entity_type' => $creditLimitChangeRequest->entity_type,
                        'entity_id' => $creditLimitChangeRequest->entity_id,
                        'new_limit' => $creditLimitChangeRequest->requested_credit_limit,
                    ]);
                }
            }

            DB::commit();

            Log::info('Credit limit change request approved', [
                'request_id' => $creditLimitChangeRequest->id,
                'approved_by' => $user->id,
                'approval_level' => $userApproval->approval_level,
            ]);

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request approved successfully.',
                ]);
            }

            return redirect()->route('credit-limit-requests.show', $creditLimitChangeRequest)
                ->with('success', 'Request approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving credit limit change request', [
                'error' => $e->getMessage(),
                'request_id' => $creditLimitChangeRequest->id,
                'user_id' => $user->id,
            ]);

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve request. Please try again.',
                ], 500);
            }

            return back()->with('error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * Reject a credit limit change request
     */
    public function reject(Request $request, CreditLimitChangeRequest $creditLimitChangeRequest)
    {
        $user = Auth::user();
        
        // Check permission
        if (!in_array('Manage Credit Limits', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage credit limits.');
        }

        // Verify business access
        if ($creditLimitChangeRequest->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Find user's approval record
        $userApproval = $creditLimitChangeRequest->approvals()
            ->where('approver_id', $user->id)
            ->whereNull('action')
            ->first();

        if (!$userApproval) {
            return back()->with('error', 'You are not authorized to reject this request.');
        }

        DB::beginTransaction();
        try {
            // Update the approval record
            $userApproval->update([
                'action' => 'rejected',
                'comment' => $validated['rejection_reason'],
            ]);

            // Reject the entire request
            $creditLimitChangeRequest->update([
                'status' => 'rejected',
                'rejected_by_user_id' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            DB::commit();

            Log::info('Credit limit change request rejected', [
                'request_id' => $creditLimitChangeRequest->id,
                'rejected_by' => $user->id,
                'approval_level' => $userApproval->approval_level,
            ]);

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request rejected successfully.',
                ]);
            }

            return redirect()->route('credit-limit-requests.show', $creditLimitChangeRequest)
                ->with('success', 'Request rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting credit limit change request', [
                'error' => $e->getMessage(),
                'request_id' => $creditLimitChangeRequest->id,
                'user_id' => $user->id,
            ]);

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject request. Please try again.',
                ], 500);
            }

            return back()->with('error', 'Failed to reject request. Please try again.');
        }
    }
}

