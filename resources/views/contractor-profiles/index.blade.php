<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">

                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Contractor Profiles</h2>
                    @if(in_array('Add Contractor Profile', (array) (Auth::user()->permissions ?? [])))
                        <a href="{{ route('contractor-profiles.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create New Contractor Profile
                        </a>
                    @endif
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

                <!-- Livewire Component -->
                @livewire('contractor-profiles.list-contractor-profiles')
            </div>
        </div>
    </div>
</x-app-layout> 