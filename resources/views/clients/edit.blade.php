<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Client') }}
            </h2>
            <a href="{{ route('clients.show', $client) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Client
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Surname *</label>
                                    <input type="text" name="surname" id="surname" value="{{ old('surname', $client->surname) }}" required
                                           placeholder="e.g., Smith"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('surname')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $client->first_name) }}" required
                                           placeholder="e.g., John"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('first_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                    <input type="text" name="other_names" id="other_names" value="{{ old('other_names', $client->other_names) }}"
                                           placeholder="e.g., Michael"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('other_names')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="sex" class="block text-sm font-medium text-gray-700 mb-2">Sex *</label>
                                    <select name="sex" id="sex" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        <option value="">Select Sex</option>
                                        <option value="male" {{ old('sex', $client->sex) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('sex', $client->sex) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('sex', $client->sex) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('sex')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $client->date_of_birth) }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('date_of_birth')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                    <select name="marital_status" id="marital_status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('marital_status', $client->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status', $client->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status', $client->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status', $client->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    @error('marital_status')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                    <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $client->occupation) }}"
                                           placeholder="e.g., Engineer"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('occupation')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Contact Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $client->phone_number) }}" required
                                           placeholder="e.g., 0770123456 or +256770123456"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('phone_number')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $client->email) }}"
                                           placeholder="e.g., john.smith@email.com"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                                <textarea name="address" id="address" rows="3" required
                                          placeholder="e.g., Plot 123, Main Street, Kampala"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">{{ old('address', $client->address) }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Identification -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Identification</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nin" class="block text-sm font-medium text-gray-700 mb-2">NIN</label>
                                    <input type="text" name="nin" id="nin" value="{{ old('nin', $client->nin) }}"
                                           placeholder="14-digit NIN"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nin')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="id_passport_no" class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
                                    <input type="text" name="id_passport_no" id="id_passport_no" value="{{ old('id_passport_no', $client->id_passport_no) }}"
                                           placeholder="e.g., B12345678XYZ"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('id_passport_no')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Services and Payment -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Services & Payment</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div>
                                        <label for="services_category" class="block text-sm font-medium text-gray-700 mb-2">Services Category</label>
                                        <select name="services_category" id="services_category"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                            <option value="">Select Category</option>
                                            <option value="dental" {{ old('services_category', $client->services_category) == 'dental' ? 'selected' : '' }}>Dental</option>
                                            <option value="optical" {{ old('services_category', $client->services_category) == 'optical' ? 'selected' : '' }}>Optical</option>
                                            <option value="outpatient" {{ old('services_category', $client->services_category) == 'outpatient' ? 'selected' : '' }}>Outpatient</option>
                                            <option value="inpatient" {{ old('services_category', $client->services_category) == 'inpatient' ? 'selected' : '' }}>Inpatient</option>
                                            <option value="maternity" {{ old('services_category', $client->services_category) == 'maternity' ? 'selected' : '' }}>Maternity</option>
                                            <option value="funeral" {{ old('services_category', $client->services_category) == 'funeral' ? 'selected' : '' }}>Funeral</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Credit and Long-Stay Options -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                        <div class="flex items-center p-4 border border-gray-300 rounded-md">
                                            <input type="checkbox" name="is_credit_eligible" id="is_credit_eligible" value="1" 
                                                   {{ old('is_credit_eligible', $client->is_credit_eligible) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="is_credit_eligible" class="ml-3 text-sm font-medium text-gray-700">
                                                Enable Credit Services
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1 ml-7">Adds /C suffix to visit ID</p>
                                        </div>
                                        
                                        <div class="flex items-center p-4 border border-gray-300 rounded-md">
                                            <input type="checkbox" name="is_long_stay" id="is_long_stay" value="1" 
                                                   {{ old('is_long_stay', $client->is_long_stay) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="is_long_stay" class="ml-3 text-sm font-medium text-gray-700">
                                                Long Stay / Inpatient
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1 ml-7">Adds /M suffix to visit ID (won't expire until discharged)</p>
                                        </div>
                                    </div>
                                    
                                    <div id="max_credit_section" class="mt-6" style="display: none;">
                                        <label for="max_credit" class="block text-sm font-medium text-gray-700 mb-2">Maximum Credit Limit (UGX)</label>
                                        <input type="number" name="max_credit" id="max_credit" value="{{ old('max_credit', $client->max_credit ?? $business->max_first_party_credit_limit) }}" 
                                               min="0" step="0.01"
                                               placeholder="Enter maximum credit limit"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        <p class="text-xs text-gray-500 mt-1">
                                            Maximum credit amount this client can have outstanding. 
                                            <strong>Defaults to:</strong> {{ $business->max_first_party_credit_limit ? number_format($business->max_first_party_credit_limit, 2) . ' UGX (Business First Party Credit Limit)' : 'Not set - please configure in Business Settings' }}
                                        </p>
                                    </div>
                                    @error('services_category')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="preferred_payment_method" class="block text-sm font-medium text-gray-700 mb-2">Preferred Payment Method</label>
                                    <select name="preferred_payment_method" id="preferred_payment_method" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        <option value="">Select Method</option>
                                        <option value="mobile_money" {{ old('preferred_payment_method', $client->preferred_payment_method) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                        <option value="cash" disabled>Cash (Coming Soon)</option>
                                        <option value="bank_transfer" disabled>Bank Transfer (Coming Soon)</option>
                                        <option value="credit_card" disabled>Credit Card (Coming Soon)</option>
                                        <option value="insurance" disabled>Insurance (Coming Soon)</option>
                                        <option value="postpaid" disabled>Postpaid (Coming Soon)</option>
                                    </select>
                                    @error('preferred_payment_method')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6" id="payment_phone_section" style="display: {{ old('preferred_payment_method', $client->preferred_payment_method) == 'mobile_money' ? 'block' : 'none' }};">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Phone Number</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="payment_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Payment Phone Number</label>
                                        <input type="tel" name="payment_phone_number" id="payment_phone_number" value="{{ old('payment_phone_number', $client->payment_phone_number) }}" 
                                               placeholder="e.g., 0770123456 or +256770123456"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        @error('payment_phone_number')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" id="same_as_phone_btn" 
                                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                                            Same as Phone Number
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">This number will be used for mobile money payments</p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Status</h3>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Client Status *</label>
                                <select name="status" id="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Next of Kin Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Next of Kin Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="nok_surname" class="block text-sm font-medium text-gray-700 mb-2">Surname</label>
                                    <input type="text" name="nok_surname" id="nok_surname" value="{{ old('nok_surname', $client->nok_surname) }}"
                                           placeholder="e.g., Smith"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_surname')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nok_first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" name="nok_first_name" id="nok_first_name" value="{{ old('nok_first_name', $client->nok_first_name) }}"
                                           placeholder="e.g., Jane"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_first_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nok_other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                    <input type="text" name="nok_other_names" id="nok_other_names" value="{{ old('nok_other_names', $client->nok_other_names) }}"
                                           placeholder="e.g., Marie"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_other_names')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="nok_marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                    <select name="nok_marital_status" id="nok_marital_status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('nok_marital_status', $client->nok_marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('nok_marital_status', $client->nok_marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('nok_marital_status', $client->nok_marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('nok_marital_status', $client->nok_marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    @error('nok_marital_status')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nok_occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                    <input type="text" name="nok_occupation" id="nok_occupation" value="{{ old('nok_occupation', $client->nok_occupation) }}"
                                           placeholder="e.g., Teacher"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_occupation')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="nok_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" name="nok_phone_number" id="nok_phone_number" value="{{ old('nok_phone_number', $client->nok_phone_number) }}"
                                           placeholder="e.g., 0770123456 or +256770123456"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_phone_number')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nok_physical_address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <input type="text" name="nok_physical_address" id="nok_physical_address" value="{{ old('nok_physical_address', $client->nok_physical_address) }}"
                                           placeholder="e.g., Plot 456, Second Street, Kampala"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    @error('nok_physical_address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-6">
                            <a href="{{ route('clients.show', $client) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-[#011478] hover:bg-[#011478]/90 text-white font-bold py-2 px-4 rounded">
                                Update Client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle max credit field based on credit eligible checkbox
            const creditEligibleCheckbox = document.getElementById('is_credit_eligible');
            const maxCreditSection = document.getElementById('max_credit_section');
            const maxCreditInput = document.getElementById('max_credit');
            
            function toggleMaxCreditSection() {
                if (creditEligibleCheckbox && maxCreditSection) {
                    if (creditEligibleCheckbox.checked) {
                        maxCreditSection.style.display = 'block';
                    } else {
                        maxCreditSection.style.display = 'none';
                        if (maxCreditInput) {
                            maxCreditInput.value = '';
                        }
                    }
                }
            }
            
            // Check on page load
            toggleMaxCreditSection();
            
            // Listen for changes
            if (creditEligibleCheckbox) {
                creditEligibleCheckbox.addEventListener('change', toggleMaxCreditSection);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodSelect = document.getElementById('preferred_payment_method');
            const paymentPhoneSection = document.getElementById('payment_phone_section');
            const paymentPhoneInput = document.getElementById('payment_phone_number');
            const sameAsPhoneBtn = document.getElementById('same_as_phone_btn');
            const clientPhoneInput = document.getElementById('phone_number');

            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'mobile_money') {
                    paymentPhoneSection.style.display = 'block';
                    paymentPhoneInput.required = true;
                } else {
                    paymentPhoneSection.style.display = 'none';
                    paymentPhoneInput.required = false;
                    paymentPhoneInput.value = '';
                }
            });

            sameAsPhoneBtn.addEventListener('click', function() {
                const clientPhone = clientPhoneInput.value;
                if (clientPhone) {
                    paymentPhoneInput.value = clientPhone;
                } else {
                    alert('Please enter the client\'s phone number first.');
                }
            });

            // Show payment phone section if mobile money is already selected
            if (paymentMethodSelect.value === 'mobile_money') {
                paymentPhoneSection.style.display = 'block';
                paymentPhoneInput.required = true;
            }

            // Toggle max credit field based on credit eligible checkbox
            const creditEligibleCheckbox = document.getElementById('is_credit_eligible');
            const maxCreditSection = document.getElementById('max_credit_section');
            const maxCreditInput = document.getElementById('max_credit');
            
            function toggleMaxCreditSection() {
                if (creditEligibleCheckbox && maxCreditSection) {
                    if (creditEligibleCheckbox.checked) {
                        maxCreditSection.style.display = 'block';
                    } else {
                        maxCreditSection.style.display = 'none';
                        if (maxCreditInput) {
                            maxCreditInput.value = '';
                        }
                    }
                }
            }
            
            // Check on page load
            toggleMaxCreditSection();
            
            // Listen for changes
            if (creditEligibleCheckbox) {
                creditEligibleCheckbox.addEventListener('change', toggleMaxCreditSection);
            }
        });
    </script>
</x-app-layout>
