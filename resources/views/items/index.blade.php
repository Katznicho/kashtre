
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Items</h2>

                    <div class="flex space-x-2">
                        {{-- @if(Auth::user()->business_id == 1) --}}
                        <a href="{{ route('items.create') }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            ➕ Create Item
                        </a>
                        {{-- @endif --}}
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('simple-items')" id="simple-tab" class="tab-button border-b-2 border-[#011478] py-2 px-1 text-sm font-medium text-[#011478] dark:text-blue-400">
                            📦 Simple Items (Goods & Services)
                        </button>
                        <button onclick="switchTab('composite-items')" id="composite-tab" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                            📋 Composite Items (Packages & Bulk)
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    <!-- Simple Items Tab Content -->
                    <div id="simple-items-content" class="tab-content">
                        <div class="mb-4">
                            <a href="{{ route('items.bulk-upload') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition duration-150">
                                📤 Goods & Services Upload
                            </a>
                        </div>
                        
                        <!-- Livewire Component for Simple Items -->
                        @livewire('items.list-simple-items')
                    </div>

                    <!-- Composite Items Tab Content -->
                    <div id="composite-items-content" class="tab-content hidden">
                        <div class="mb-4">
                            <a href="{{ route('package-bulk-upload.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-md hover:bg-purple-700 transition duration-150">
                                📦 Packages & Bulk Upload
                            </a>
                        </div>
                        
                        <!-- Livewire Component for Composite Items -->
                        @livewire('items.list-composite-items')
                    </div>
                </div>

                <script>
                    function switchTab(tabName) {
                        console.log('Switching to tab:', tabName); // Debug log
                        
                        // Hide all tab contents
                        const tabContents = document.querySelectorAll('.tab-content');
                        tabContents.forEach(content => {
                            content.classList.add('hidden');
                            console.log('Hiding content:', content.id); // Debug log
                        });

                        // Remove active class from all tabs
                        const tabs = document.querySelectorAll('.tab-button');
                        tabs.forEach(tab => {
                            tab.classList.remove('border-blue-500', 'text-blue-600');
                            tab.classList.add('border-transparent', 'text-gray-500');
                        });

                        // Show selected tab content
                        const selectedContent = document.getElementById(tabName + '-content');
                        if (selectedContent) {
                            selectedContent.classList.remove('hidden');
                            console.log('Showing content:', selectedContent.id); // Debug log
                        } else {
                            console.log('Content not found for:', tabName + '-content'); // Debug log
                        }

                        // Add active class to selected tab
                        const selectedTab = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
                        if (selectedTab) {
                            selectedTab.classList.remove('border-transparent', 'text-gray-500');
                            selectedTab.classList.add('border-blue-500', 'text-blue-600');
                        }
                    }

                    // Initialize tabs on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('DOM loaded, initializing tabs'); // Debug log
                        // Set the first tab as active by default
                        switchTab('simple-items');
                    });
                </script>
</x-app-layout>
