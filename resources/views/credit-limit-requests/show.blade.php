<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Credit Limit Change Request Details') }}
            </h2>
            <a href="{{ route('credit-limit-requests.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Requests
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Request Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Request Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Entity</p>
                            <p class="text-sm font-medium text-gray-900">{{ $creditLimitChangeRequest->entity_name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $creditLimitChangeRequest->entity_type)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @if($creditLimitChangeRequest->status === 'approved') bg-green-100 text-green-800
                                @elseif($creditLimitChangeRequest->status === 'rejected') bg-red-100 text-red-800
                                @elseif($creditLimitChangeRequest->status === 'authorized') bg-blue-100 text-blue-800
                                @elseif($creditLimitChangeRequest->status === 'initiated') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($creditLimitChangeRequest->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Current Credit Limit</p>
                            <p class="text-sm font-medium text-gray-900">
                                UGX {{ number_format($creditLimitChangeRequest->current_credit_limit, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requested Credit Limit</p>
                            <p class="text-sm font-medium text-gray-900">
                                UGX {{ number_format($creditLimitChangeRequest->requested_credit_limit, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Increase Amount</p>
                            <p class="text-sm font-medium text-green-600">
                                + UGX {{ number_format($creditLimitChangeRequest->requested_credit_limit - $creditLimitChangeRequest->current_credit_limit, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Initiated By</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $creditLimitChangeRequest->initiatedBy->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $creditLimitChangeRequest->initiated_at?->format('M d, Y H:i') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    @if($creditLimitChangeRequest->reason)
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Reason</p>
                            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                                {{ $creditLimitChangeRequest->reason }}
                            </p>
                        </div>
                    @endif

                    @if($creditLimitChangeRequest->rejection_reason)
                        <div class="mb-4">
                            <p class="text-sm text-red-500 mb-1">Rejection Reason</p>
                            <p class="text-sm text-gray-900 bg-red-50 p-3 rounded-md">
                                {{ $creditLimitChangeRequest->rejection_reason }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Approval Status</h3>
                    
                    <!-- Authorizers -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Authorizers (Level 2)</h4>
                        <div class="space-y-2">
                            @php
                                $authorizerApprovals = $creditLimitChangeRequest->approvals->where('approval_level', 'authorizer');
                            @endphp
                            @forelse($authorizerApprovals as $approval)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $approval->approver->name ?? 'N/A' }}</p>
                                        @if($approval->comment)
                                            <p class="text-xs text-gray-500 mt-1">{{ $approval->comment }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        @if($approval->isPending())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($approval->isApproved())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
                                        @elseif($approval->isRejected())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No authorizers assigned.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Approvers -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Approvers (Level 3)</h4>
                        <div class="space-y-2">
                            @php
                                $approverApprovals = $creditLimitChangeRequest->approvals->where('approval_level', 'approver');
                            @endphp
                            @forelse($approverApprovals as $approval)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $approval->approver->name ?? 'N/A' }}</p>
                                        @if($approval->comment)
                                            <p class="text-xs text-gray-500 mt-1">{{ $approval->comment }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        @if($approval->isPending())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($approval->isApproved())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
                                        @elseif($approval->isRejected())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No approvers assigned.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Actions (if user can approve) -->
            @if($canApprove)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ $approvalLevel === 'authorizer' ? 'Authorize' : 'Approve' }} Request
                        </h3>

                        <form method="POST" action="{{ route('credit-limit-requests.approve', $creditLimitChangeRequest) }}" class="mb-4">
                            @csrf
                            <div class="mb-4">
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                    Comment (Optional)
                                </label>
                                <textarea name="comment" 
                                          id="comment" 
                                          rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-green-700">
                                {{ $approvalLevel === 'authorizer' ? 'Approve & Authorize' : 'Approve' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('credit-limit-requests.reject', $creditLimitChangeRequest) }}">
                            @csrf
                            <div class="mb-4">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rejection Reason <span class="text-red-500">*</span>
                                </label>
                                <textarea name="rejection_reason" 
                                          id="rejection_reason" 
                                          rows="3"
                                          required
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-red-700">
                                Reject Request
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

