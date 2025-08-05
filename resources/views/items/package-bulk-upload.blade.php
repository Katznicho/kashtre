<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Bulk Upload Packages & Bulk Items</h2>
                    <a href="{{ route('items.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Download Section -->
                    <div class="bg-purple-50 dark:bg-purple-900 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-200 mb-4">
                            Step 1: Download Template
                        </h3>
                        <p class="text-purple-700 dark:text-purple-300 mb-4">
                            Download the Excel template for packages and bulk items. This template includes available items for selection.
                        </p>
                        
                        <div class="space-y-3">
                            <button onclick="showTemplateModal()"
                                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                📥 Download Template
                            </button>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div class="bg-orange-50 dark:bg-orange-900 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200 mb-4">
                            Step 2: Upload Filled Template
                        </h3>
                        <p class="text-orange-700 dark:text-orange-300 mb-4">
                            Upload your filled template to import packages and bulk items.
                        </p>
                        
                        <form action="{{ route('package-bulk-upload.import') }}" 
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
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB. Supported formats: .xlsx, .xls</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit" 
                                        :disabled="uploading"
                                        class="bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded inline-flex items-center">
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

                <!-- Information Section -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">
                        📋 Important Information
                    </h3>
                    <div class="text-blue-700 dark:text-blue-300 space-y-2">
                        <p><strong>Packages:</strong> Items that include other items with maximum quantities and validity periods.</p>
                        <p><strong>Bulk Items:</strong> Items that include other items with fixed quantities.</p>
                        <p><strong>Template Structure:</strong> The template includes separate sheets for packages and bulk items.</p>
                        <p><strong>Available Items:</strong> Only goods and services can be included in packages and bulk items.</p>
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
                    Select a business to download a template for packages and bulk items. This template includes all available goods and services items for selection.
                </p>

                <form id="templateForm" action="{{ route('package-bulk-upload.template') }}" method="GET">
                    @if(Auth::user()->business_id == 1)
                    <div class="mb-4">
                        <label for="template_business_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Business <span class="text-red-500">*</span>
                        </label>
                        <select name="business_id" id="template_business_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition duration-150">
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