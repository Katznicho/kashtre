<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Bulk Upload Goods & Services</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('items.bulk-upload.validation-guide') }}" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                            📋 Validation Guide
                        </a>
                        <a href="{{ route('items.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to List
                        </a>
                    </div>
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

                @if(session('import_errors'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        <h4 class="font-bold">Import Errors:</h4>
                        <ul class="list-disc list-inside mt-2">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Contractor Validation Notice -->
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                Important: Contractor Validation Rules
                            </h3>
                            <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>When Hospital Share < 100%:</strong> Contractor selection is <strong>REQUIRED</strong></li>
                                    <li><strong>When Hospital Share = 100%:</strong> Contractor should be <strong>LEFT EMPTY</strong></li>
                                    <li>Only contractors associated with your business will be available in the dropdown</li>
                                </ul>
                                <p class="mt-2">
                                    <a href="{{ route('items.bulk-upload.validation-guide') }}" class="font-medium underline hover:text-orange-600">
                                        View complete validation guide →
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Download Section -->
                    <div class="bg-blue-50 dark:bg-blue-900 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">
                            Step 1: Download Template
                        </h3>
                        <p class="text-blue-700 dark:text-blue-300 mb-4">
                            Download the Excel template with dropdown data included. This template is for goods and services only.
                        </p>
                        
                        <div class="space-y-3">
                            <button onclick="showTemplateModal()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                📥 Download Template
                            </button>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div class="bg-green-50 dark:bg-green-900 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-4">
                            Step 2: Upload Filled Template
                        </h3>
                        <p class="text-green-700 dark:text-green-300 mb-4">
                            Upload your filled template to import goods and services items.
                        </p>
                        
                        <form action="{{ route('items.bulk-upload.import') }}" 
                              method="POST" 
                              enctype="multipart/form-data" 
                              class="space-y-4"
                              x-data="{ uploading: false }"
                              @submit="uploading = true">
                            @csrf
                            
                            <!-- Business Selection for Upload -->
                            @if(Auth::user()->business_id == 1)
                            <div>
                                <label for="upload_business_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Business <span class="text-red-500">*</span>
                                </label>
                                <select name="business_id" id="upload_business_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        required>
                                    <option value="">Select a business</option>
                                    @foreach($businesses as $business)
                                        <option value="{{ $business->id }}">
                                            {{ $business->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <div>
                                <div class="p-3 bg-green-50 dark:bg-green-900 rounded-md mb-4">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        <strong>Business:</strong> {{ Auth::user()->business->name }}
                                    </p>
                                </div>
                                <input type="hidden" name="business_id" value="{{ Auth::user()->business_id }}">
                            </div>
                            @endif
                            
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
                </div>
            </div>
        </div>
    </div>

    <!-- Business Selection Modal for Template Download -->
    <div id="templateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Select Business for Template</h3>
                    <button onclick="hideTemplateModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <p class="text-sm text-gray-600 mb-4">
                    Select a business to download a template with dropdown data for that business. This template includes all available groups, departments, units, service points, and contractors.
                </p>

                <form id="templateForm" action="{{ route('items.bulk-upload.template') }}" method="GET">
                    @if(Auth::user()->business_id == 1)
                    <div class="mb-4">
                        <label for="template_business_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Business <span class="text-red-500">*</span>
                        </label>
                        <select name="business_id" id="template_business_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a business</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="mb-4">
                        <div class="p-3 bg-green-50 dark:bg-green-900 rounded-md mb-4">
                            <p class="text-sm text-green-700 dark:text-green-300">
                                <strong>Business:</strong> {{ Auth::user()->business->name }}
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                Your business is automatically selected for this template.
                            </p>
                        </div>
                        <input type="hidden" name="business_id" value="{{ Auth::user()->business_id }}">
                    </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideTemplateModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                            Download Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTemplateModal() {
            document.getElementById('templateModal').classList.remove('hidden');
        }

        function hideTemplateModal() {
            document.getElementById('templateModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('templateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideTemplateModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideTemplateModal();
            }
        });
    </script>
</x-app-layout> 