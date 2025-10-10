<x-app-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-[#011478] to-[#1e40af] rounded-lg shadow-lg mb-8">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/10 rounded-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Withdrawal Setting Details</h1>
                                <p class="text-blue-100 mt-1">{{ $withdrawalSetting->business->name ?? 'Unknown Business' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Status Badge -->
                            <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full {{ $withdrawalSetting->is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $withdrawalSetting->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if(in_array('Edit Withdrawal Settings', (array) $permissions ?? []))
                            <a href="{{ route('withdrawal-settings.edit', $withdrawalSetting->uuid) }}" class="inline-flex items-center px-4 py-2 bg-white text-[#011478] text-sm font-semibold rounded-md hover:bg-gray-100 transition duration-150">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                <!-- Key Metrics Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Minimum Amount</p>
                                <p class="text-2xl font-bold">UGX {{ number_format($withdrawalSetting->minimum_withdrawal_amount) }}</p>
                            </div>
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Free Withdrawals</p>
                                <p class="text-2xl font-bold">{{ $withdrawalSetting->number_of_free_withdrawals_per_day }}</p>
                                <p class="text-green-100 text-xs">per day</p>
                            </div>
                            <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Business Approvers</p>
                                <p class="text-2xl font-bold">{{ $withdrawalSetting->min_business_approvers }}</p>
                                <p class="text-purple-100 text-xs">minimum required</p>
                            </div>
                            <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">Kashtre Approvers</p>
                                <p class="text-2xl font-bold">{{ $withdrawalSetting->min_kashtre_approvers }}</p>
                                <p class="text-orange-100 text-xs">minimum required</p>
                            </div>
                            <svg class="w-8 h-8 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Business Information -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            Business Information
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Business Name</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->business->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Business Email</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->business->email ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Business Phone</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->business->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-medium text-gray-500">Business Type</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $withdrawalSetting->business_id == 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $withdrawalSetting->business_id == 1 ? 'Kashtre (Super Business)' : 'Regular Business' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawal Configuration -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            Withdrawal Configuration
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Withdrawal Type</span>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $withdrawalSetting->withdrawal_type === 'express' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    {{ ucfirst($withdrawalSetting->withdrawal_type) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Minimum Withdrawal Amount</span>
                                <span class="text-sm font-semibold text-gray-900">UGX {{ number_format($withdrawalSetting->minimum_withdrawal_amount) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-500">Free Withdrawals Per Day</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->number_of_free_withdrawals_per_day }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-medium text-gray-500">Total Required Approvals</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->min_business_approvers + $withdrawalSetting->min_kashtre_approvers }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-gray-100 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            System Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-500 mb-2">Created At</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $withdrawalSetting->created_at->format('H:i:s') }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-500 mb-2">Last Updated</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $withdrawalSetting->updated_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $withdrawalSetting->updated_at->format('H:i:s') }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-500 mb-2">Setting ID</p>
                                <p class="text-xs font-mono text-gray-900 break-all">{{ $withdrawalSetting->uuid }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approvers Section -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Business Approvers -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                Business Approvers
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    {{ $withdrawalSetting->businessApprovers->count() }} selected
                                </span>
                            </h3>
                            <p class="text-sm text-purple-600 mt-1">Minimum required: {{ $withdrawalSetting->min_business_approvers }}</p>
                        </div>
                        <div class="px-6 py-4">
                            @if($withdrawalSetting->businessApprovers->count() > 0)
                                <div class="space-y-3">
                                    @foreach($withdrawalSetting->businessApprovers as $approver)
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    @if($approver->approver_type === 'user')
                                                        {{ $approver->approver->name ?? 'Unknown User' }}
                                                    @else
                                                        {{ $approver->approver->user->name ?? 'Unknown Contractor' }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ ucfirst($approver->approver_type) }} • {{ $approver->approver->email ?? 'No email' }}
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 italic">No business approvers assigned</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kashtre Approvers -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-orange-100">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <div class="p-2 bg-orange-100 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                Kashtre Approvers
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    {{ $withdrawalSetting->kashtreApprovers->count() }} selected
                                </span>
                            </h3>
                            <p class="text-sm text-orange-600 mt-1">Minimum required: {{ $withdrawalSetting->min_kashtre_approvers }}</p>
                        </div>
                        <div class="px-6 py-4">
                            @if($withdrawalSetting->kashtreApprovers->count() > 0)
                                <div class="space-y-3">
                                    @foreach($withdrawalSetting->kashtreApprovers as $approver)
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    @if($approver->approver_type === 'user')
                                                        {{ $approver->approver->name ?? 'Unknown User' }}
                                                    @else
                                                        {{ $approver->approver->user->name ?? 'Unknown Contractor' }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ ucfirst($approver->approver_type) }} • {{ $approver->approver->email ?? 'No email' }}
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="text-gray-500 italic">No Kashtre approvers assigned</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Last updated {{ $withdrawalSetting->updated_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if(in_array('Edit Withdrawal Settings', (array) $permissions ?? []))
                            <a href="{{ route('withdrawal-settings.edit', $withdrawalSetting->uuid) }}" class="inline-flex items-center px-6 py-3 bg-[#011478] text-white text-sm font-semibold rounded-lg hover:bg-[#011478]/90 transition duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Setting
                            </a>
                            @endif
                            <a href="{{ route('withdrawal-settings.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-150 shadow-sm">
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
        </div>
    </div>
</x-app-layout>
