<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create Withdrawal Setting</h2>
                    <a href="{{ route('withdrawal-settings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Withdrawal Settings
                    </a>
                </div>

                @if($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('withdrawal-settings.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Business Selection -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Business <span class="text-red-500">*</span>
                            </label>
                            <select name="business_id" id="business_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                <option value="">Select Business</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Withdrawal Type -->
                        <div>
                            <label for="withdrawal_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Withdrawal Type <span class="text-red-500">*</span>
                            </label>
                            <select name="withdrawal_type" id="withdrawal_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                <option value="">Select Type</option>
                                @foreach($withdrawalTypes as $key => $value)
                                    <option value="{{ $value }}" {{ old('withdrawal_type', 'regular') == $value ? 'selected' : '' }}>
                                        {{ ucfirst($value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Minimum Withdrawal Amount -->
                        <div>
                            <label for="minimum_withdrawal_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Minimum Withdrawal Amount (UGX) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="minimum_withdrawal_amount" id="minimum_withdrawal_amount" 
                                   value="{{ old('minimum_withdrawal_amount', 500) }}" 
                                   min="0" step="0.01" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                   placeholder="Enter minimum withdrawal amount">
                        </div>

                        <!-- Number of Free Withdrawals Per Day -->
                        <div>
                            <label for="number_of_free_withdrawals_per_day" class="block text-sm font-medium text-gray-700 mb-2">
                                Number of Free Withdrawals Per Day <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="number_of_free_withdrawals_per_day" id="number_of_free_withdrawals_per_day" 
                                   value="{{ old('number_of_free_withdrawals_per_day', 1) }}" 
                                   min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                   placeholder="Enter number of free withdrawals">
                        </div>
                    </div>

                    <!-- Maximum Approval Time -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Maximum Approval Time</h3>
                        <p class="text-sm text-yellow-700 mb-4">Set the maximum time allowed for withdrawal approval. If exceeded, funds will be automatically refunded.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_approval_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Maximum Approval Time
                                </label>
                                <input type="number" name="max_approval_time" id="max_approval_time" 
                                       value="{{ old('max_approval_time') }}" 
                                       min="1" step="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                       placeholder="e.g., 24">
                            </div>
                            
                            <div>
                                <label for="max_approval_time_unit" class="block text-sm font-medium text-gray-700 mb-2">
                                    Time Unit
                                </label>
                                <select name="max_approval_time_unit" id="max_approval_time_unit" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="hours" {{ old('max_approval_time_unit', 'hours') == 'hours' ? 'selected' : '' }}>Hours</option>
                                    <option value="days" {{ old('max_approval_time_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                </select>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Leave empty to disable automatic refund on timeout.</p>
                    </div>

                    <!-- 3-Level Approval Configuration -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-4">3-Level Approval Configuration</h3>
                        <p class="text-sm text-blue-700 mb-6">Configure the minimum and maximum number of approvers for each level (1-2 people per level).</p>
                        
                        <!-- Business Approval Levels -->
                        <div class="space-y-6">
                            <h4 class="text-md font-semibold text-gray-800">Business Approval Levels</h4>
                            
                            <!-- Level 1: Initiators -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_business_initiators" class="block text-sm font-medium text-gray-700">Min Initiators</label>
                                    <input type="number" name="min_business_initiators" id="min_business_initiators" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_business_initiators" class="block text-sm font-medium text-gray-700">Max Initiators</label>
                                    <input type="number" name="max_business_initiators" id="max_business_initiators" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 1: Initiators</label>
                                    <span class="text-xs text-gray-500">People who can initiate withdrawal requests</span>
                                </div>
                            </div>

                            <!-- Level 2: Authorizers -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_business_authorizers" class="block text-sm font-medium text-gray-700">Min Authorizers</label>
                                    <input type="number" name="min_business_authorizers" id="min_business_authorizers" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_business_authorizers" class="block text-sm font-medium text-gray-700">Max Authorizers</label>
                                    <input type="number" name="max_business_authorizers" id="max_business_authorizers" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 2: Authorizers</label>
                                    <span class="text-xs text-gray-500">People who authorize withdrawal requests</span>
                                </div>
                            </div>

                            <!-- Level 3: Approvers -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_business_approvers" class="block text-sm font-medium text-gray-700">Min Approvers</label>
                                    <input type="number" name="min_business_approvers" id="min_business_approvers" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_business_approvers" class="block text-sm font-medium text-gray-700">Max Approvers</label>
                                    <input type="number" name="max_business_approvers" id="max_business_approvers" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                        <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 3: Approvers</label>
                                    <span class="text-xs text-gray-500">People who give final approval</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kashtre Approval Levels -->
                        <div class="space-y-6 mt-8">
                            <h4 class="text-md font-semibold text-gray-800">Kashtre Approval Levels</h4>
                            
                            <!-- Level 1: Initiators -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_kashtre_initiators" class="block text-sm font-medium text-gray-700">Min Initiators</label>
                                    <input type="number" name="min_kashtre_initiators" id="min_kashtre_initiators" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_kashtre_initiators" class="block text-sm font-medium text-gray-700">Max Initiators</label>
                                    <input type="number" name="max_kashtre_initiators" id="max_kashtre_initiators" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 1: Initiators</label>
                                    <span class="text-xs text-gray-500">Kashtre users who can initiate</span>
                                </div>
                            </div>

                            <!-- Level 2: Authorizers -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_kashtre_authorizers" class="block text-sm font-medium text-gray-700">Min Authorizers</label>
                                    <input type="number" name="min_kashtre_authorizers" id="min_kashtre_authorizers" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_kashtre_authorizers" class="block text-sm font-medium text-gray-700">Max Authorizers</label>
                                    <input type="number" name="max_kashtre_authorizers" id="max_kashtre_authorizers" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 2: Authorizers</label>
                                    <span class="text-xs text-gray-500">Kashtre users who can authorize</span>
                                </div>
                            </div>

                            <!-- Level 3: Approvers -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg border">
                                <div>
                                    <label for="min_kashtre_approvers" class="block text-sm font-medium text-gray-700">Min Approvers</label>
                                    <input type="number" name="min_kashtre_approvers" id="min_kashtre_approvers" min="1" max="2" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="max_kashtre_approvers" class="block text-sm font-medium text-gray-700">Max Approvers</label>
                                    <input type="number" name="max_kashtre_approvers" id="max_kashtre_approvers" min="1" max="2" value="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                        <div>
                                    <label class="block text-sm font-medium text-gray-700">Level 3: Approvers</label>
                                    <span class="text-xs text-gray-500">Kashtre users who give final approval</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                        </label>
                    </div>

                    <!-- Business Approvers Selection - 3 Levels -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Business Approvers Selection <span class="text-red-500">*</span>
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">All users for the selected business are shown.</p>
                        
                        <div id="business-approvers-container" class="space-y-6">
                            <div class="text-gray-500 text-sm italic">Please select a business first</div>
                        </div>
                        @error('business_approvers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kashtre Approvers Selection - 3 Levels -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Kashtre Approvers Selection <span class="text-red-500">*</span>
                        </h3>
                        
                        <div id="kashtre-approvers-container" class="space-y-6">
                            <!-- Level 1: Initiators -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                <h4 class="text-md font-semibold text-gray-700 mb-3">Level 1: Initiators</h4>
                                <div class="mb-3">
                                    <input type="text" 
                                           id="search-kashtre-initiators" 
                                           placeholder="Search initiators by name or email..." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                </div>
                                <div class="space-y-2" id="kashtre-initiators-list">
                                    @foreach($users->where('business_id', 1) as $user)
                                        <div class="flex items-center space-x-3 kashtre-initiator-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                            <input type="checkbox" 
                                                   name="kashtre_initiators[]" 
                                                   value="user:{{ $user->id }}"
                                                   id="kashtre_initiator_{{ $user->id }}"
                                                   class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                            <label for="kashtre_initiator_{{ $user->id }}" class="text-sm text-gray-900">
                                                {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Level 2: Authorizers -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                <h4 class="text-md font-semibold text-gray-700 mb-3">Level 2: Authorizers</h4>
                                <div class="mb-3">
                                    <input type="text" 
                                           id="search-kashtre-authorizers" 
                                           placeholder="Search authorizers by name or email..." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                </div>
                                <div class="space-y-2" id="kashtre-authorizers-list">
                                    @foreach($users->where('business_id', 1) as $user)
                                        <div class="flex items-center space-x-3 kashtre-authorizer-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                            <input type="checkbox" 
                                                   name="kashtre_authorizers[]" 
                                                   value="user:{{ $user->id }}"
                                                   id="kashtre_authorizer_{{ $user->id }}"
                                                   class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                            <label for="kashtre_authorizer_{{ $user->id }}" class="text-sm text-gray-900">
                                                {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                        </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Level 3: Approvers -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                <h4 class="text-md font-semibold text-gray-700 mb-3">Level 3: Approvers</h4>
                                <div class="mb-3">
                                    <input type="text" 
                                           id="search-kashtre-approvers" 
                                           placeholder="Search approvers by name or email..." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                </div>
                                <div class="space-y-2" id="kashtre-approvers-list">
                            @foreach($users->where('business_id', 1) as $user)
                                <div class="flex items-center space-x-3 kashtre-approver-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                    <input type="checkbox" 
                                           name="kashtre_approvers[]" 
                                           value="user:{{ $user->id }}"
                                                   id="kashtre_approver_{{ $user->id }}"
                                           class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                            <label for="kashtre_approver_{{ $user->id }}" class="text-sm text-gray-900">
                                        {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                                </div>
                            </div>
                        </div>
                        @error('kashtre_approvers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('kashtre_authorizers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('kashtre_initiators')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('withdrawal-settings.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#011478]">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#011478] hover:bg-[#011478]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#011478]">
                            Create Withdrawal Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript to handle search functionality for Kashtre approvers -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality for Kashtre approver levels
            const kashtreSearchInputs = {
                'search-kashtre-initiators': '.kashtre-initiator-item',
                'search-kashtre-authorizers': '.kashtre-authorizer-item',
                'search-kashtre-approvers': '.kashtre-approver-item'
            };

            Object.keys(kashtreSearchInputs).forEach(searchId => {
                const searchInput = document.getElementById(searchId);
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase().trim();
                        const itemSelector = kashtreSearchInputs[searchId];
                        const items = document.querySelectorAll(itemSelector);
                        
                        items.forEach(item => {
                            const name = item.getAttribute('data-name') || '';
                            const email = item.getAttribute('data-email') || '';
                            
                            if (searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm)) {
                                item.style.display = 'flex';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    });
                }
            });
        });
    </script>

    <!-- JavaScript to handle 3-level approval system -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const businessSelect = document.getElementById('business_id');
            const businessApproversContainer = document.getElementById('business-approvers-container');
            
            // All users data
            const allUsers = @json($users);
            
            // Filter business approvers when business is selected
            businessSelect.addEventListener('change', function() {
                const selectedBusinessId = parseInt(this.value);
                
                if (!selectedBusinessId) {
                    businessApproversContainer.innerHTML = '<div class="text-gray-500 text-sm italic">Please select a business first</div>';
                    return;
                }
                
                // Clear current content
                businessApproversContainer.innerHTML = '';
                
                // Filter users by selected business
                const businessUsers = allUsers.filter(user => user.business_id === selectedBusinessId);
                
                if (businessUsers.length === 0) {
                    businessApproversContainer.innerHTML = '<div class="text-gray-500 text-sm italic">No approvers available for this business</div>';
                    return;
                }
                
                // Create 3-level approval structure
                const levels = [
                    { name: 'initiators', title: 'Level 1: Initiators', description: 'People who can initiate withdrawal requests' },
                    { name: 'authorizers', title: 'Level 2: Authorizers', description: 'People who authorize withdrawal requests' },
                    { name: 'approvers', title: 'Level 3: Approvers', description: 'People who give final approval' }
                ];
                
                levels.forEach(level => {
                    const levelDiv = document.createElement('div');
                    levelDiv.className = 'border border-gray-200 rounded-lg p-4 bg-white';
                    
                    levelDiv.innerHTML = `
                        <h4 class="text-md font-semibold text-gray-700 mb-3">${level.title}</h4>
                        <p class="text-xs text-gray-500 mb-3">${level.description}</p>
                        <div class="mb-3">
                            <input type="text" 
                                   id="search-business-${level.name}" 
                                   placeholder="Search ${level.name} by name or email..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                        </div>
                        <div class="space-y-2" id="business-${level.name}-container">
                        </div>
                    `;
                    
                    const container = levelDiv.querySelector(`#business-${level.name}-container`);
                    
                    businessUsers.forEach(user => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'flex items-center space-x-3 business-' + level.name + '-item';
                        checkboxDiv.setAttribute('data-name', user.name.toLowerCase());
                        checkboxDiv.setAttribute('data-email', user.email.toLowerCase());
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" 
                                   name="business_${level.name}[]" 
                                   value="user:${user.id}"
                                   id="business_${level.name}_${user.id}"
                                   class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                            <label for="business_${level.name}_${user.id}" class="text-sm text-gray-900">
                                ${user.name} <span class="text-gray-500">(${user.email})</span>
                            </label>
                        `;
                        container.appendChild(checkboxDiv);
                    });
                    
                    businessApproversContainer.appendChild(levelDiv);
                    
                    // Add search functionality for this level
                    const searchInput = levelDiv.querySelector(`#search-business-${level.name}`);
                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase().trim();
                            const items = container.querySelectorAll('.business-' + level.name + '-item');
                            
                            items.forEach(item => {
                                const name = item.getAttribute('data-name') || '';
                                const email = item.getAttribute('data-email') || '';
                                
                                if (searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm)) {
                                    item.style.display = 'flex';
                                } else {
                                    item.style.display = 'none';
                                }
                            });
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>
