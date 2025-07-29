
<x-app-layout>
    <div class="py-12" x-data="userForm()" x-init="init()" @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Users</h2>

                    @if(in_array('Add Staff', $permissions))
                    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                        ➕ Create User
                    </a>
                    @endif
                </div>

                @livewire('list-users')
            </div>
        </div>


    </div>

</x-app-layout>
