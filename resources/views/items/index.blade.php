
<x-app-layout>
    <div class="py-12" x-data="userForm()" x-init="init()" @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Items</h2>

                    <div class="flex space-x-2">
                        @if(Auth::user()->business_id == 1)
                        <a href="{{ route('items.bulk-upload') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition duration-150">
                            📤 Bulk Upload
                        </a>
                        <a href="{{ route('items.bulk-upload.template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition duration-150">
                            📥 Download Template
                        </a>
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

</x-app-layout>
