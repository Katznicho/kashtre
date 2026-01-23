<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Insurance Company') }}
            </h2>
            <a href="{{ route('settings.index', ['tab' => 'insurance-companies']) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium">Please fix the following errors:</h3>
                                    <ul class="mt-2 list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('insurance-companies.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <!-- Company Information Section -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Company Information
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Company Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Company Name <span class="text-red-500">*</span>
                                    </label>
                                    <select name="name" 
                                            id="name" 
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                                        <option value="">-- Select Insurance Company --</option>
                                        @foreach($insuranceCompanyNames as $companyName)
                                            <option value="{{ $companyName }}" {{ old('name') == $companyName ? 'selected' : '' }}>{{ $companyName }}</option>
                                        @endforeach
                                    </select>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Company Code (Auto-generated) -->
                                <div>
                                    <label for="code_display" class="block text-sm font-medium text-gray-700 mb-1">
                                        Company Code <span class="text-gray-400">(8-digit, auto-generated)</span>
                                    </label>
                                    <div class="mt-1 relative">
                                        <input type="text" 
                                               id="code_display" 
                                               value="Will be generated on save"
                                               readonly
                                               disabled
                                               class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm font-mono text-gray-600 cursor-not-allowed">
                                        <input type="hidden" name="code" value="">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">An 8-digit code will be automatically generated when you save</p>
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email') }}"
                                           required
                                           placeholder="Enter company email address"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('email') border-red-300 @enderror">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           value="{{ old('phone') }}"
                                           placeholder="Enter company phone number"
                                           maxlength="20"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('phone') border-red-300 @enderror">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                        Address
                                    </label>
                                    <textarea name="address" 
                                              id="address" 
                                              rows="2"
                                              placeholder="Enter company address"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('address') border-red-300 @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Head Office Address -->
                                <div class="md:col-span-2">
                                    <label for="head_office_address" class="block text-sm font-medium text-gray-700 mb-1">
                                        Head Office Address
                                    </label>
                                    <textarea name="head_office_address" 
                                              id="head_office_address" 
                                              rows="2"
                                              placeholder="Enter head office address"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('head_office_address') border-red-300 @enderror">{{ old('head_office_address') }}</textarea>
                                    @error('head_office_address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Postal Address -->
                                <div class="md:col-span-2">
                                    <label for="postal_address" class="block text-sm font-medium text-gray-700 mb-1">
                                        Postal Address
                                    </label>
                                    <textarea name="postal_address" 
                                              id="postal_address" 
                                              rows="2"
                                              placeholder="Enter postal address"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('postal_address') border-red-300 @enderror">{{ old('postal_address') }}</textarea>
                                    @error('postal_address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Website -->
                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                        Website
                                    </label>
                                    <input type="url" 
                                           name="website" 
                                           id="website" 
                                           value="{{ old('website') }}"
                                           placeholder="Enter company website URL"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('website') border-red-300 @enderror">
                                    @error('website')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Description
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              placeholder="Enter company description"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Admin User Account Section -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Admin User Account
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">Create an admin user account for the third-party system. A password reset link will be sent to the user's email.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- User Email -->
                                <div>
                                    <label for="user_email" class="block text-sm font-medium text-gray-700 mb-1">
                                        User Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="user_email" 
                                           id="user_email" 
                                           value="{{ old('user_email') }}"
                                           required
                                           placeholder="Enter admin user email"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('user_email') border-red-300 @enderror">
                                    @error('user_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Username -->
                                <div>
                                    <label for="user_username" class="block text-sm font-medium text-gray-700 mb-1">
                                        Username <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="user_username" 
                                           id="user_username" 
                                           value="{{ old('user_username') }}"
                                           required
                                           placeholder="Enter username for login"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('user_username') border-red-300 @enderror">
                                    @error('user_username')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Info Message -->
                                <div class="md:col-span-2">
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700">
                                                    <strong>Note:</strong> A password reset link will be sent to the user's email address. They will need to set their password using that link.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('settings.index', ['tab' => 'insurance-companies']) }}" 
                               class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Insurance Company
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
