<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-4 sm:mb-0">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Register New Client</h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v2H7V5zm6 4H7v2h6V9zm0 4H7v2h6v-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $business->name }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $currentBranch->name }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Clients
                        </a>
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('clients.store') }}" method="POST" class="space-y-8">
                    @csrf
                    
                    <!-- Personal Information Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Personal Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Names -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">
                                        Surname <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="surname" id="surname" value="{{ old('surname') }}" required 
                                           placeholder="Enter surname"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required 
                                           placeholder="Enter first name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                    <input type="text" name="other_names" id="other_names" value="{{ old('other_names') }}" 
                                           placeholder="Middle names (optional)"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                            </div>

                            <!-- Identification -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nin" class="block text-sm font-medium text-gray-700 mb-2">
                                        National ID (NIN) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nin" id="nin" value="{{ old('nin') }}" required
                                           placeholder="14-digit NIN number (leave empty to auto-generate)"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate temporary ID</p>
                                </div>
                                
                                <div>
                                    <label for="tin_number" class="block text-sm font-medium text-gray-700 mb-2">TIN Number</label>
                                    <div class="space-y-2">
                                        <input type="text" name="tin_number" id="tin_number" value="{{ old('tin_number') }}" 
                                               placeholder="Tax Identification Number"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <button type="button" id="same_as_nin_btn" 
                                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                            Same as NIN Number
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Demographics -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="sex" class="block text-sm font-medium text-gray-700 mb-2">
                                        Gender <span class="text-red-500">*</span>
                                    </label>
                                    <select name="sex" id="sex" required 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('sex') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('sex') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('sex') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Marital Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="marital_status" id="marital_status" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Occupation <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="occupation" id="occupation" value="{{ old('occupation') }}" required
                                       placeholder="e.g., Teacher, Engineer, Business Owner"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Contact Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required 
                                           placeholder="e.g., 0770123456 or +256770123456"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">This is the primary contact number for communication</p>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                           placeholder="e.g., client@example.com"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">Use institution's default email if client has none</p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="county" class="block text-sm font-medium text-gray-700 mb-2">
                                        County <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="county" id="county" value="{{ old('county') }}" required
                                           placeholder="Enter county name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="village" class="block text-sm font-medium text-gray-700 mb-2">
                                        Village <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="village" id="village" value="{{ old('village') }}" required
                                           placeholder="Enter village name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service & Payment Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-600">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Service & Payment Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="services_category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Services Category <span class="text-red-500">*</span>
                                </label>
                                <select name="services_category" id="services_category" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <option value="">Select Category</option>
                                    <option value="dental" {{ old('services_category') == 'dental' ? 'selected' : '' }}>Dental</option>
                                    <option value="optical" {{ old('services_category') == 'optical' ? 'selected' : '' }}>Optical</option>
                                    <option value="outpatient" {{ old('services_category') == 'outpatient' ? 'selected' : '' }}>Outpatient</option>
                                    <option value="inpatient" {{ old('services_category') == 'inpatient' ? 'selected' : '' }}>Inpatient</option>
                                    <option value="maternity" {{ old('services_category') == 'maternity' ? 'selected' : '' }}>Maternity</option>
                                    <option value="funeral" {{ old('services_category') == 'funeral' ? 'selected' : '' }}>Funeral</option>
                                </select>
                            </div>

                            <!-- Payment Methods -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Payment Methods <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-600 mb-4">Select all payment methods this client can use in order of preference</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_insurance" value="insurance"
                                               {{ in_array('insurance', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_insurance" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">1</span>
                                            🛡️ Insurance
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_credit_arrangement" value="credit_arrangement"
                                               {{ in_array('credit_arrangement', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_credit_arrangement" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">2</span>
                                            💳 Credit Arrangement
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_mobile_money" value="mobile_money"
                                               {{ in_array('mobile_money', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_mobile_money" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">3</span>
                                            📱 MM (Mobile Money)
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_v_card" value="v_card"
                                               {{ in_array('v_card', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_v_card" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">4</span>
                                            💳 V Card (Virtual Card)
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_p_card" value="p_card"
                                               {{ in_array('p_card', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_p_card" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">5</span>
                                            💳 P Card (Physical Card)
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_bank_transfer" value="bank_transfer"
                                               {{ in_array('bank_transfer', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_bank_transfer" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">6</span>
                                            🏦 Bank Transfer
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="payment_methods[]" id="payment_cash" value="cash"
                                               {{ in_array('cash', old('payment_methods', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="payment_cash" class="ml-3 text-sm font-medium text-gray-700">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mr-2">7</span>
                                            💵 Cash
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-4 mt-4">
                                    <button type="button" id="select_all_payments" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        Select All
                                    </button>
                                    <button type="button" id="clear_all_payments" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                        Clear All
                                    </button>
                                </div>
                            </div>

                            <!-- Payment Phone Number Section -->
                            <div id="payment_phone_section" style="display: none;" class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-900 mb-3">Payment Phone Number</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="payment_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Payment Phone Number</label>
                                        <input type="tel" name="payment_phone_number" id="payment_phone_number" value="{{ old('payment_phone_number') }}" 
                                               placeholder="e.g., 0770123456 or +256770123456"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <p class="text-xs text-gray-500 mt-1">This number will be used specifically for mobile money payments</p>
                                    </div>
                                    
                                    <div class="flex items-end">
                                        <button type="button" id="same_as_phone_btn" 
                                                class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                            Same as Contact Phone
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-blue-700 mt-2">This number is different from the contact phone number and is used exclusively for payment transactions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Next of Kin Information Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-orange-600 to-red-600">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Next of Kin Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Names -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="nok_surname" class="block text-sm font-medium text-gray-700 mb-2">
                                        Surname <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nok_surname" id="nok_surname" value="{{ old('nok_surname') }}" required
                                           placeholder="Enter next of kin surname"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="nok_first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nok_first_name" id="nok_first_name" value="{{ old('nok_first_name') }}" required
                                           placeholder="Enter next of kin first name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="nok_other_names" class="block text-sm font-medium text-gray-700 mb-2">Other Names</label>
                                    <input type="text" name="nok_other_names" id="nok_other_names" value="{{ old('nok_other_names') }}" 
                                           placeholder="Middle names (optional)"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                            </div>
                            
                            <!-- Demographics -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label for="nok_sex" class="block text-sm font-medium text-gray-700 mb-2">
                                        Gender <span class="text-red-500">*</span>
                                    </label>
                                    <select name="nok_sex" id="nok_sex" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('nok_sex') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('nok_sex') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('nok_sex') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="nok_marital_status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Marital Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="nok_marital_status" id="nok_marital_status" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('nok_marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('nok_marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('nok_marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('nok_marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="nok_occupation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Occupation <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nok_occupation" id="nok_occupation" value="{{ old('nok_occupation') }}" required
                                           placeholder="e.g., Teacher, Engineer"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="nok_phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="nok_phone_number" id="nok_phone_number" value="{{ old('nok_phone_number') }}" required
                                           placeholder="e.g., 0770123456"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">Contact number for next of kin</p>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nok_county" class="block text-sm font-medium text-gray-700 mb-2">
                                        County <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nok_county" id="nok_county" value="{{ old('nok_county') }}" required
                                           placeholder="Enter county name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                                
                                <div>
                                    <label for="nok_village" class="block text-sm font-medium text-gray-700 mb-2">
                                        Village <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nok_village" id="nok_village" value="{{ old('nok_village') }}" required
                                           placeholder="Enter village name"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <a href="{{ route('clients.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Register Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentPhoneSection = document.getElementById('payment_phone_section');
            const paymentPhoneInput = document.getElementById('payment_phone_number');
            const sameAsPhoneBtn = document.getElementById('same_as_phone_btn');
            const clientPhoneInput = document.getElementById('phone_number');
            const mobileMoneyCheckbox = document.getElementById('payment_mobile_money');
            const selectAllBtn = document.getElementById('select_all_payments');
            const clearAllBtn = document.getElementById('clear_all_payments');
            const paymentCheckboxes = document.querySelectorAll('input[name="payment_methods[]"]');
            
            // TIN same as NIN functionality
            const sameAsNinBtn = document.getElementById('same_as_nin_btn');
            const ninInput = document.getElementById('nin');
            const tinInput = document.getElementById('tin_number');

            function togglePaymentPhoneSection() {
                if (mobileMoneyCheckbox.checked) {
                    paymentPhoneSection.style.display = 'block';
                    paymentPhoneInput.required = true;
                } else {
                    paymentPhoneSection.style.display = 'none';
                    paymentPhoneInput.required = false;
                    paymentPhoneInput.value = '';
                }
            }

            mobileMoneyCheckbox.addEventListener('change', togglePaymentPhoneSection);
            
            sameAsPhoneBtn.addEventListener('click', function() {
                paymentPhoneInput.value = clientPhoneInput.value;
            });

            sameAsNinBtn.addEventListener('click', function() {
                tinInput.value = ninInput.value;
            });

            selectAllBtn.addEventListener('click', function() {
                paymentCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                togglePaymentPhoneSection();
            });

            clearAllBtn.addEventListener('click', function() {
                paymentCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                togglePaymentPhoneSection();
            });

            // Initialize on page load
            if (mobileMoneyCheckbox.checked) {
                togglePaymentPhoneSection();
            }
        });
    </script>
</x-app-layout>
