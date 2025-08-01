<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Bulk Upload Contractor Profiles</h2>
                    <a href="{{ route('contractor-profiles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('errors'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        <h4 class="font-bold">Import Errors:</h4>
                        <ul class="list-disc list-inside mt-2">
                            @foreach(session('errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Template Download Section -->
                    <div class="space-y-6">
                        <div class="bg-blue-50 dark:bg-blue-900 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">
                                Step 1: Download Template
                            </h3>
                            <p class="text-blue-700 dark:text-blue-300 mb-4">
                                Download the Excel template and fill it with your contractor profile data. 
                                Make sure to follow the format exactly as shown in the template.
                            </p>
                            
                            <div class="bg-white dark:bg-gray-700 p-4 rounded border">
                                <h4 class="font-semibold text-gray-800 dark:text-white mb-2">Template Columns:</h4>
                                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                    <li><strong>Business Name:</strong> Exact business name (required)</li>
                                    <li><strong>User Email:</strong> Email of existing user (required)</li>
                                    <li><strong>Bank Name:</strong> Name of the bank (required)</li>
                                    <li><strong>Account Name:</strong> Name on the account (required)</li>
                                    <li><strong>Account Number:</strong> Bank account number (required)</li>
                                    <li><strong>Account Balance:</strong> Current balance (optional, default: 0.00)</li>
                                    <li><strong>Kashtre Account Number:</strong> Kashtre account (optional)</li>
                                    <li><strong>Signing Qualifications:</strong> Qualifications (optional)</li>
                                </ul>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('contractor-profiles.bulk-upload.template') }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Template
                                </a>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                                Important Instructions
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>Business name must match exactly with existing business names in the system</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>User email must belong to an existing user in the system</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>User must belong to the specified business</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>Each user can only have one contractor profile</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>Non-super admin users can only create profiles for their own business</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">•</span>
                                    <span>Account balance must be a number (0 or greater)</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div class="space-y-6">
                        <div class="bg-green-50 dark:bg-green-900 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-4">
                                Step 2: Upload Filled Template
                            </h3>
                            
                            <form action="{{ route('contractor-profiles.bulk-upload.import') }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  class="space-y-4"
                                  x-data="{ uploading: false }"
                                  @submit="uploading = true">
                                @csrf
                                
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Excel File <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" 
                                           name="file" 
                                           id="file" 
                                           accept=".xlsx,.xls" 
                                           required
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB. Supported formats: .xlsx, .xls</p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <button type="submit" 
                                            :disabled="uploading"
                                            class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <svg x-show="uploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-show="!uploading">Upload & Import</span>
                                        <span x-show="uploading">Uploading...</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Available Businesses -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                                Available Businesses
                            </h3>
                            <div class="space-y-2">
                                @foreach($businesses as $business)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-600 rounded border">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $business->name }}
                                        </span>
                                        @if(Auth::user()->business_id == 1)
                                            <span class="text-xs text-gray-500">All businesses accessible</span>
                                        @else
                                            <span class="text-xs text-blue-600">Your business</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 