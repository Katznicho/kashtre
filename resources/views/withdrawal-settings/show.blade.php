<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Withdrawal Setting Details</h2>
                    <div class="flex items-center space-x-3">
                        @if(in_array('Edit Withdrawal Settings', (array) $permissions ?? []))
                        <a href="{{ route('withdrawal-settings.edit', $withdrawalSetting->uuid) }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        @endif
                        <a href="{{ route('withdrawal-settings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $withdrawalSetting->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $withdrawalSetting->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Business Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Business Information
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Business Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->business->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Business Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->business->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Business Phone</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->business->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawal Configuration -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Withdrawal Configuration
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Withdrawal Type</label>
                                <span class="inline-flex mt-1 px-2 py-1 text-xs font-semibold rounded-full {{ $withdrawalSetting->withdrawal_type === 'express' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($withdrawalSetting->withdrawal_type) }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum Withdrawal Amount</label>
                                <p class="mt-1 text-sm text-gray-900">UGX {{ number_format($withdrawalSetting->minimum_withdrawal_amount) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Free Withdrawals Per Day</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->number_of_free_withdrawals_per_day }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Requirements -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Approval Requirements
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum Business Approvers</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->min_business_approvers }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Minimum Kashtre Approvers</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->min_kashtre_approvers }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Minimum Approvals</label>
                                <p class="mt-1 text-sm text-gray-900 font-medium">{{ $withdrawalSetting->min_business_approvers + $withdrawalSetting->min_kashtre_approvers }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            System Information
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->created_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $withdrawalSetting->updated_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Setting ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $withdrawalSetting->uuid }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Approvers -->
                <div class="mt-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                Business Approvers ({{ $withdrawalSetting->businessApprovers->count() }})
                            </h3>
                        </div>
                        <div class="px-6 py-4">
                            @if($withdrawalSetting->businessApprovers->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($withdrawalSetting->businessApprovers as $approver)
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($approver->approver_type === 'user')
                                                        {{ $approver->approver->name ?? 'Unknown User' }}
                                                    @else
                                                        {{ $approver->approver->user->name ?? 'Unknown Contractor' }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ ucfirst($approver->approver_type) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No business approvers assigned</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kashtre Approvers -->
                <div class="mt-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Kashtre Approvers ({{ $withdrawalSetting->kashtreApprovers->count() }})
                            </h3>
                        </div>
                        <div class="px-6 py-4">
                            @if($withdrawalSetting->kashtreApprovers->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($withdrawalSetting->kashtreApprovers as $approver)
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($approver->approver_type === 'user')
                                                        {{ $approver->approver->name ?? 'Unknown User' }}
                                                    @else
                                                        {{ $approver->approver->user->name ?? 'Unknown Contractor' }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ ucfirst($approver->approver_type) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No Kashtre approvers assigned</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex items-center justify-end pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        @if(in_array('Edit Withdrawal Settings', (array) $permissions ?? []))
                        <a href="{{ route('withdrawal-settings.edit', $withdrawalSetting->uuid) }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Setting
                        </a>
                        @endif
                        <a href="{{ route('withdrawal-settings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
