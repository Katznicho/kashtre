
<x-app-layout>
    <div class="py-12" x-data="userForm()" x-init="init()" @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Admins</h2>

                    <div class="flex items-center space-x-3">
                        @if(in_array('Add Admin Users', (array) $permissions))
                        <a href="{{ route('admins.create') }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            ➕ Create Admin
                        </a>
                        @endif

                        @if(in_array('Add Admin Users', (array) $permissions))
                        <a href="{{ route('admins.bulk.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition duration-150">
                            📥 Download Template
                        </a>
                        @endif

                        @if(in_array('Add Admin Users', (array) $permissions))
                        <button onclick="document.getElementById('bulkUploadForm').classList.toggle('hidden')" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-semibold rounded-md hover:bg-orange-700 transition duration-150">
                            📤 Bulk Upload
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Bulk Upload Form -->
                @if(in_array('Add Admin Users', (array) $permissions))
                <div id="bulkUploadForm" class="hidden mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <form action="{{ route('admins.bulk.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Admin Template</label>
                                <input type="file" name="template" accept=".xlsx,.xls" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition duration-150">
                                Upload Template
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Upload an Excel file (.xlsx or .xls) with admin data. Download the template first to see the required format.</p>
                    </form>
                </div>
                @endif

                @livewire('admin.admins')
            </div>
        </div>

    </div>

</x-app-layout>
