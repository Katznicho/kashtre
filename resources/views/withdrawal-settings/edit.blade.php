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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Min Business Approvers -->
                        <div>
                            <label for="min_business_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                Minimum Business Approvers <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="min_business_approvers" id="min_business_approvers" 
                                   value="{{ old('min_business_approvers', $withdrawalSetting->min_business_approvers) }}" 
                                   min="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                   placeholder="Enter minimum business approvers">
                        </div>

                        <!-- Min Kashtre Approvers -->
                        <div>
                            <label for="min_kashtre_approvers" class="block text-sm font-medium text-gray-700 mb-2">
                                Minimum Kashtre Approvers <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="min_kashtre_approvers" id="min_kashtre_approvers" 
                                   value="{{ old('min_kashtre_approvers', $withdrawalSetting->min_kashtre_approvers) }}" 
                                   min="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                   placeholder="Enter minimum Kashtre approvers">
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
                        <label for="business_approvers" class="block text-sm font-medium text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Business Approvers <span class="text-red-500">*</span>
                            <span class="ml-2 text-xs text-gray-500">(Select at least <span id="min-business-display">{{ $withdrawalSetting->min_business_approvers }}</span>)</span>
                        </label>
                        <select name="business_approvers[]" id="business_approvers" multiple required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                style="min-height: 150px;">
                            <optgroup label="Users">
                                @foreach($users as $user)
                                    <option value="user:{{ $user->id }}" 
                                        {{ $withdrawalSetting->businessApprovers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </optgroup>
                            @if(Auth::user()->business_id != 1)
                                <optgroup label="Contractors">
                                    @foreach($contractors as $contractor)
                                        <option value="contractor:{{ $contractor->id }}"
                                            {{ $withdrawalSetting->businessApprovers->where('approver_type', 'contractor')->where('approver_id', $contractor->id)->count() > 0 ? 'selected' : '' }}>
                                            {{ $contractor->user->name ?? 'Unknown' }} (Contractor)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple approvers</p>
                        @error('business_approvers')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kashtre Approvers Selection -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <label for="kashtre_approvers" class="block text-sm font-medium text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Kashtre Approvers <span class="text-red-500">*</span>
                            <span class="ml-2 text-xs text-gray-500">(Select at least <span id="min-kashtre-display">{{ $withdrawalSetting->min_kashtre_approvers }}</span>)</span>
                        </label>
                        <select name="kashtre_approvers[]" id="kashtre_approvers" multiple required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent"
                                style="min-height: 150px;">
                            <optgroup label="Users">
                                @foreach($users as $user)
                                    <option value="user:{{ $user->id }}"
                                        {{ $withdrawalSetting->kashtreApprovers->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </optgroup>
                            @if(Auth::user()->business_id != 1)
                                <optgroup label="Contractors">
                                    @foreach($contractors as $contractor)
                                        <option value="contractor:{{ $contractor->id }}"
                                            {{ $withdrawalSetting->kashtreApprovers->where('approver_type', 'contractor')->where('approver_id', $contractor->id)->count() > 0 ? 'selected' : '' }}>
                                            {{ $contractor->user->name ?? 'Unknown' }} (Contractor)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple approvers</p>
                        @error('kashtre_approvers')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            Update Withdrawal Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript to update minimum display values -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const minBusinessInput = document.getElementById('min_business_approvers');
            const minKashtreInput = document.getElementById('min_kashtre_approvers');
            const minBusinessDisplay = document.getElementById('min-business-display');
            const minKashtreDisplay = document.getElementById('min-kashtre-display');
            
            // Update display when minimum values change
            minBusinessInput.addEventListener('input', function() {
                minBusinessDisplay.textContent = this.value || 3;
            });
            
            minKashtreInput.addEventListener('input', function() {
                minKashtreDisplay.textContent = this.value || 3;
            });
        });
    </script>
</x-app-layout>
