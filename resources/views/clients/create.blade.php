<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Register New Client</h1>
                        <p class="text-gray-600 mt-2">{{ $business->name }} - {{ $currentBranch->name }}</p>
                        <p class="text-sm text-blue-600 mt-1">Client will be registered at: <strong>{{ $currentBranch->name }}</strong></p>
                    </div>
                    <a href="{{ route('clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Clients
                    </a>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clients.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-6">
                    @csrf
                    
                    <!-- Client Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Client Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Surname *</label>
                                <input type="text" name="surname" id="surname" value="{{ old('surname') }}" required 
                                       placeholder="Enter client's surname"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required 
                                       placeholder="Enter client's first name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                <input type="text" name="other_names" id="other_names" value="{{ old('other_names') }}" 
                                       placeholder="Enter middle names (optional)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <label for="nin" class="block text-sm font-medium text-gray-700 mb-2">National ID (NIN)</label>
                                <input type="text" name="nin" id="nin" value="{{ old('nin') }}" 
                                       placeholder="Enter 14-digit NIN number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="id_passport_no" class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
                                <input type="text" name="id_passport_no" id="id_passport_no" value="{{ old('id_passport_no') }}" 
                                       placeholder="Enter ID or passport number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="sex" class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select name="sex" id="sex" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('sex') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('sex') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('sex') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                <select name="marital_status" id="marital_status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Status</option>
                                    <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                <input type="text" name="occupation" id="occupation" value="{{ old('occupation') }}" 
                                       placeholder="e.g., Teacher, Engineer, Business Owner"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required 
                                       placeholder="e.g., 0770123456 or +256770123456"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                       placeholder="e.g., client@example.com"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address *</label>
                            <textarea name="address" id="address" rows="3" required 
                                      placeholder="Enter complete physical address including district, village, etc."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">{{ old('address') }}</textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="services_category" class="block text-sm font-medium text-gray-700 mb-2">Services Category</label>
                                <select name="services_category" id="services_category" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="dental" {{ old('services_category') == 'dental' ? 'selected' : '' }}>Dental</option>
                                    <option value="optical" {{ old('services_category') == 'optical' ? 'selected' : '' }}>Optical</option>
                                    <option value="outpatient" {{ old('services_category') == 'outpatient' ? 'selected' : '' }}>Outpatient</option>
                                    <option value="inpatient" {{ old('services_category') == 'inpatient' ? 'selected' : '' }}>Inpatient</option>
                                    <option value="maternity" {{ old('services_category') == 'maternity' ? 'selected' : '' }}>Maternity</option>
                                    <option value="funeral" {{ old('services_category') == 'funeral' ? 'selected' : '' }}>Funeral</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="preferred_payment_method" class="block text-sm font-medium text-gray-700 mb-2">Preferred Payment Method</label>
                                <select name="preferred_payment_method" id="preferred_payment_method" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Method</option>
                                    <option value="mobile_money" {{ old('preferred_payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="cash" disabled>Cash (Coming Soon)</option>
                                    <option value="bank_transfer" disabled>Bank Transfer (Coming Soon)</option>
                                    <option value="credit_card" disabled>Credit Card (Coming Soon)</option>
                                    <option value="insurance" disabled>Insurance (Coming Soon)</option>
                                    <option value="postpaid" disabled>Postpaid (Coming Soon)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment Phone Number Section -->
                        <div class="mt-6" id="payment_phone_section" style="display: none;">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Phone Number</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="payment_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Payment Phone Number</label>
                                    <input type="tel" name="payment_phone_number" id="payment_phone_number" value="{{ old('payment_phone_number') }}" 
                                           placeholder="e.g., 0770123456 or +256770123456"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
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
                    
                    <!-- Next of Kin Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Next of Kin Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="nok_surname" class="block text-sm font-medium text-gray-700 mb-2">Surname</label>
                                <input type="text" name="nok_surname" id="nok_surname" value="{{ old('nok_surname') }}" 
                                       placeholder="Enter next of kin surname"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="nok_first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="nok_first_name" id="nok_first_name" value="{{ old('nok_first_name') }}" 
                                       placeholder="Enter next of kin first name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="nok_other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                <input type="text" name="nok_other_names" id="nok_other_names" value="{{ old('nok_other_names') }}" 
                                       placeholder="Enter middle names (optional)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <label for="nok_marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                <select name="nok_marital_status" id="nok_marital_status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                                    <option value="">Select Status</option>
                                    <option value="single" {{ old('nok_marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ old('nok_marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="divorced" {{ old('nok_marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="widowed" {{ old('nok_marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="nok_occupation" class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                <input type="text" name="nok_occupation" id="nok_occupation" value="{{ old('nok_occupation') }}" 
                                       placeholder="e.g., Teacher, Engineer, Business Owner"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="nok_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="nok_phone_number" id="nok_phone_number" value="{{ old('nok_phone_number') }}" 
                                       placeholder="e.g., 0770123456 or +256770123456"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="nok_physical_address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                            <textarea name="nok_physical_address" id="nok_physical_address" rows="3" 
                                      placeholder="Enter next of kin's complete physical address"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">{{ old('nok_physical_address') }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <a href="{{ route('clients.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="bg-[#011478] text-white px-6 py-2 rounded-lg hover:bg-[#011478]/90 transition-colors">
                            <i class="fas fa-save mr-2"></i>Register Client
                        </button>
                    </div>
                </form>

                <!-- JavaScript for Payment Method Handling -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const paymentMethodSelect = document.getElementById('preferred_payment_method');
                        const paymentPhoneSection = document.getElementById('payment_phone_section');
                        const paymentPhoneInput = document.getElementById('payment_phone_number');
                        const sameAsPhoneBtn = document.getElementById('same_as_phone_btn');
                        const clientPhoneInput = document.getElementById('phone_number');

                        // Show/hide payment phone section based on payment method
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

                        // Handle "Same as Phone Number" button
                        sameAsPhoneBtn.addEventListener('click', function() {
                            const clientPhone = clientPhoneInput.value;
                            if (clientPhone) {
                                paymentPhoneInput.value = clientPhone;
                            } else {
                                alert('Please enter the client\'s phone number first.');
                            }
                        });

                        // Show payment phone section if mobile_money is pre-selected (e.g., from validation errors)
                        if (paymentMethodSelect.value === 'mobile_money') {
                            paymentPhoneSection.style.display = 'block';
                            paymentPhoneInput.required = true;
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
