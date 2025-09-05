@props(['client', 'show' => false])

<div x-data="{ show: @js($show) }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Client Registration Confirmed</h3>
                            <p class="text-blue-100 text-sm">Ready to proceed with item selection</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-white hover:text-blue-200 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-6">
                <!-- Client Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Client Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Client Name</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $client->name }}</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Client ID:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $client->client_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Visit ID:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $client->visit_id }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <p class="text-lg font-semibold text-green-600">Active</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Age:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $client->age ?? 'N/A' }} years</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Gender:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($client->sex) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Payment Method</p>
                                <p class="text-lg font-semibold text-blue-600">
                                    @if($client->preferred_payment_method)
                                        {{ ucwords(str_replace('_', ' ', $client->preferred_payment_method)) }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($client->payment_phone_number)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Payment Phone:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $client->payment_phone_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Financial Summary -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-1">Available Balance</p>
                            <p class="text-xl font-bold text-blue-600">UGX {{ number_format($client->available_balance ?? 0, 2) }}</p>
                            @if(($client->suspense_balance ?? 0) > 0)
                                <p class="text-xs text-orange-600">({{ number_format($client->suspense_balance ?? 0, 2) }} temporary)</p>
                            @endif
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Balance</p>
                            <p class="text-lg font-semibold text-gray-700">UGX {{ number_format($client->total_balance ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-sm font-medium text-yellow-800">Ready to Proceed</h5>
                            <p class="text-sm text-yellow-700 mt-1">
                                Client registration is complete. You can now select items and services for this client. 
                                The payment method has been configured and the client is ready for transactions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button @click="show = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Close
                </button>
                <button @click="show = false; $dispatch('client-confirmed', { clientId: '{{ $client->id }}' })" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Proceed to Item Selection
                </button>
            </div>
        </div>
    </div>
</div>
