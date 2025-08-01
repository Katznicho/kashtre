
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Items</h2>

                    <div class="flex space-x-2">
                        <a href="{{ route('items.bulk-upload') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition duration-150">
                            📤 Bulk Upload
                        </a>
                        
                        <!-- Download Template Button -->
                        <a href="{{ route('items.bulk-upload.template') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            📥 Download Template
                        </a>

                        <!-- Download Reference Sheet Button -->
                        <button onclick="showReferenceModal()"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-md hover:bg-purple-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            📋 Download Reference Sheet
                        </button>
                        
                        @if(Auth::user()->business_id == 1)
                        <a href="{{ route('items.create') }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            ➕ Create Item
                        </a>
                        @endif
                    </div>
                </div>


                @livewire('items.list-items')
            </div>
        </div>
    </div>

    <!-- Business Selection Modal for Reference Sheet -->
    <div id="referenceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Select Business for Reference Sheet</h3>
                    <button onclick="hideReferenceModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <p class="text-sm text-gray-600 mb-4">
                    Select a business to download a reference sheet containing all departments, service points, contractors, and other data for that business.
                </p>

                <form id="referenceForm" action="{{ route('items.bulk-upload.reference') }}" method="GET">
                    @if(Auth::user()->business_id == 1)
                    <div class="mb-4">
                        <label for="reference_business_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Business <span class="text-red-500">*</span>
                        </label>
                        <select name="business_id" id="reference_business_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a business</option>
                            @foreach(\App\Models\Business::where('id', '!=', 1)->get() as $business)
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
                                Your business is automatically selected for this reference sheet.
                            </p>
                        </div>
                        <input type="hidden" name="business_id" value="{{ Auth::user()->business_id }}">
                    </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideReferenceModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition duration-150">
                            Download Reference Sheet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showReferenceModal() {
            document.getElementById('referenceModal').classList.remove('hidden');
        }

        function hideReferenceModal() {
            document.getElementById('referenceModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('referenceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideReferenceModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideReferenceModal();
            }
        });
    </script>
</x-app-layout>
