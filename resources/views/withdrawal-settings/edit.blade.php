<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Withdrawal Setting</h2>
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

                <form action="{{ route('withdrawal-settings.update', $withdrawalSetting->uuid) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Business Selection -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Business <span class="text-red-500">*</span>
                            </label>
                            <select name="business_id" id="business_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                <option value="">Select Business</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ (old('business_id', $withdrawalSetting->business_id) == $business->id) ? 'selected' : '' }}>
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
                                    <option value="{{ $value }}" {{ (old('withdrawal_type', $withdrawalSetting->withdrawal_type) == $value) ? 'selected' : '' }}>
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
                                   value="{{ old('minimum_withdrawal_amount', $withdrawalSetting->minimum_withdrawal_amount) }}" 
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
                                   value="{{ old('number_of_free_withdrawals_per_day', $withdrawalSetting->number_of_free_withdrawals_per_day) }}" 
                                   min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                   placeholder="Enter number of free withdrawals">
                        </div>
                    </div>

                    <!-- 3-Level Approval Configuration -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">3-Level Approval Configuration</h3>
                        
                        <!-- Business Level Approvers -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                            <div>
                                <label for="min_business_initiators" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Business Initiators <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="min_business_initiators" id="min_business_initiators" 
                                       value="{{ old('min_business_initiators', $withdrawalSetting->min_business_initiators ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_business_initiators" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Business Initiators <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_business_initiators" id="max_business_initiators" 
                                       value="{{ old('max_business_initiators', $withdrawalSetting->max_business_initiators ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="min_business_authorizers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Business Authorizers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="min_business_authorizers" id="min_business_authorizers" 
                                       value="{{ old('min_business_authorizers', $withdrawalSetting->min_business_authorizers ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_business_authorizers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Business Authorizers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_business_authorizers" id="max_business_authorizers" 
                                       value="{{ old('max_business_authorizers', $withdrawalSetting->max_business_authorizers ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        <div>
                            <label for="min_business_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Business Approvers <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="min_business_approvers" id="min_business_approvers" 
                                       value="{{ old('min_business_approvers', $withdrawalSetting->min_business_approvers ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_business_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Business Approvers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_business_approvers" id="max_business_approvers" 
                                       value="{{ old('max_business_approvers', $withdrawalSetting->max_business_approvers ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>

                        <!-- Kashtre Level Approvers -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div>
                                <label for="min_kashtre_initiators" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Kashtre Initiators <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="min_kashtre_initiators" id="min_kashtre_initiators" 
                                       value="{{ old('min_kashtre_initiators', $withdrawalSetting->min_kashtre_initiators ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_kashtre_initiators" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Kashtre Initiators <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_kashtre_initiators" id="max_kashtre_initiators" 
                                       value="{{ old('max_kashtre_initiators', $withdrawalSetting->max_kashtre_initiators ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="min_kashtre_authorizers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Kashtre Authorizers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="min_kashtre_authorizers" id="min_kashtre_authorizers" 
                                       value="{{ old('min_kashtre_authorizers', $withdrawalSetting->min_kashtre_authorizers ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_kashtre_authorizers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Kashtre Authorizers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_kashtre_authorizers" id="max_kashtre_authorizers" 
                                       value="{{ old('max_kashtre_authorizers', $withdrawalSetting->max_kashtre_authorizers ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        <div>
                            <label for="min_kashtre_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Min Kashtre Approvers <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="min_kashtre_approvers" id="min_kashtre_approvers" 
                                       value="{{ old('min_kashtre_approvers', $withdrawalSetting->min_kashtre_approvers ?? 1) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            <div>
                                <label for="max_kashtre_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                    Max Kashtre Approvers <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_kashtre_approvers" id="max_kashtre_approvers" 
                                       value="{{ old('max_kashtre_approvers', $withdrawalSetting->max_kashtre_approvers ?? 2) }}" 
                                       min="1" max="2" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $withdrawalSetting->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                        </label>
                    </div>

                    <!-- Business Approvers Selection -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Approvers Selection</h3>
                        <p class="text-sm text-gray-600 mb-4">Only business employees/staff are shown (contractors are excluded from business approval roles).</p>
                        
                        <!-- Level 1: Initiators -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 1: Initiators</h4>
                            <div id="business-initiators-container" class="space-y-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                                @foreach($users->where('business_id', $withdrawalSetting->business_id) as $user)
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               name="business_initiators[]" 
                                               value="user:{{ $user->id }}"
                                               id="business_initiator_{{ $user->id }}"
                                               {{ $withdrawalSetting->businessInitiators->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="business_initiator_{{ $user->id }}" class="text-sm text-gray-900">
                                            {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('business_initiators')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Level 2: Authorizers -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 2: Authorizers</h4>
                            <div id="business-authorizers-container" class="space-y-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                                @foreach($users->where('business_id', $withdrawalSetting->business_id) as $user)
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               name="business_authorizers[]" 
                                               value="user:{{ $user->id }}"
                                               id="business_authorizer_{{ $user->id }}"
                                               {{ $withdrawalSetting->businessAuthorizers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="business_authorizer_{{ $user->id }}" class="text-sm text-gray-900">
                                            {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('business_authorizers')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Level 3: Approvers -->
                        <div>
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 3: Approvers</h4>
                        <div id="business-approvers-container" class="space-y-3">
                            <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                            @foreach($users->where('business_id', $withdrawalSetting->business_id) as $user)
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           name="business_approvers[]" 
                                           value="user:{{ $user->id }}"
                                               id="business_approver_{{ $user->id }}"
                                           {{ $withdrawalSetting->businessApprovers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="business_approver_{{ $user->id }}" class="text-sm text-gray-900">
                                        {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('business_approvers')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>

                    <!-- Kashtre Approvers Selection -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kashtre Approvers Selection</h3>
                        
                        <!-- Level 1: Initiators -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 1: Initiators</h4>
                            <div id="kashtre-initiators-container" class="space-y-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                                @foreach($users->where('business_id', 1) as $user)
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               name="kashtre_initiators[]" 
                                               value="user:{{ $user->id }}"
                                               id="kashtre_initiator_{{ $user->id }}"
                                               {{ $withdrawalSetting->kashtreInitiators->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="kashtre_initiator_{{ $user->id }}" class="text-sm text-gray-900">
                                            {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('kashtre_initiators')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Level 2: Authorizers -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 2: Authorizers</h4>
                            <div id="kashtre-authorizers-container" class="space-y-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                                @foreach($users->where('business_id', 1) as $user)
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               name="kashtre_authorizers[]" 
                                               value="user:{{ $user->id }}"
                                               id="kashtre_authorizer_{{ $user->id }}"
                                               {{ $withdrawalSetting->kashtreAuthorizers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="kashtre_authorizer_{{ $user->id }}" class="text-sm text-gray-900">
                                            {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('kashtre_authorizers')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Level 3: Approvers -->
                        <div>
                            <h4 class="text-md font-medium text-gray-800 mb-3">Level 3: Approvers</h4>
                            <div id="kashtre-approvers-container" class="space-y-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">Users</div>
                                @foreach($users->where('business_id', 1) as $user)
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               name="kashtre_approvers[]" 
                                               value="user:{{ $user->id }}"
                                               id="kashtre_approver_{{ $user->id }}"
                                               {{ $withdrawalSetting->kashtreApprovers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                               class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                        <label for="kashtre_approver_{{ $user->id }}" class="text-sm text-gray-900">
                                            {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('kashtre_approvers')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('withdrawal-settings.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#011478]">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#011478] hover:bg-[#011478]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#011478]">
                            Update Withdrawal Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript to update minimum display values and filter approvers -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const businessSelect = document.getElementById('business_id');
            const businessInitiatorsContainer = document.getElementById('business-initiators-container');
            const businessAuthorizersContainer = document.getElementById('business-authorizers-container');
            const businessApproversContainer = document.getElementById('business-approvers-container');
            
            // All users data
            const allUsers = @json($users);
            
            // Get currently selected approvers for each level
            const getCurrentSelectedApprovers = (level) => {
                const checkboxes = document.querySelectorAll(`input[name="business_${level}[]"]:checked`);
                return Array.from(checkboxes).map(checkbox => checkbox.value);
            };
            
            // Filter business approvers when business is changed
            businessSelect.addEventListener('change', function() {
                const selectedBusinessId = parseInt(this.value);
                
                if (!selectedBusinessId) {
                    const noBusinessMessage = '<div class="text-gray-500 text-sm italic">Please select a business first</div>';
                    businessInitiatorsContainer.innerHTML = noBusinessMessage;
                    businessAuthorizersContainer.innerHTML = noBusinessMessage;
                    businessApproversContainer.innerHTML = noBusinessMessage;
                    return;
                }
                
                // Filter users by selected business
                const businessUsers = allUsers.filter(user => user.business_id === selectedBusinessId);
                
                // Function to create user checkboxes for a specific level
                const createUserCheckboxes = (level, container) => {
                    container.innerHTML = '';
                    
                    if (businessUsers.length > 0) {
                        const usersSection = document.createElement('div');
                        usersSection.innerHTML = '<div class="text-sm font-medium text-gray-700 mb-2">Users</div>';
                        
                        businessUsers.forEach(user => {
                            const checkboxDiv = document.createElement('div');
                            checkboxDiv.className = 'flex items-center space-x-3';
                            const isChecked = getCurrentSelectedApprovers(level).includes(`user:${user.id}`) ? 'checked' : '';
                            checkboxDiv.innerHTML = `
                                <input type="checkbox" 
                                       name="business_${level}[]" 
                                       value="user:${user.id}"
                                       id="business_${level}_${user.id}"
                                       ${isChecked}
                                       class="h-4 w-4 text-[#011478] focus:ring-[#011478] border-gray-300 rounded">
                                <label for="business_${level}_${user.id}" class="text-sm text-gray-900">
                                    ${user.name} <span class="text-gray-500">(${user.email})</span>
                                </label>
                            `;
                            usersSection.appendChild(checkboxDiv);
                        });
                        
                        container.appendChild(usersSection);
                    } else {
                        container.innerHTML = '<div class="text-gray-500 text-sm italic">No users available for this business</div>';
                    }
                };
                
                // Update all three levels
                createUserCheckboxes('initiators', businessInitiatorsContainer);
                createUserCheckboxes('authorizers', businessAuthorizersContainer);
                createUserCheckboxes('approvers', businessApproversContainer);
            });
        });
    </script>
</x-app-layout>
