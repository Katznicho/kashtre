<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Package Tracking
                        </h2>
                        <div class="flex space-x-3">
                            <a href="{{ route('package-tracking.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filament Table Component -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Package Tracking Records</h2>
                </div>
                
                <div class="p-6">
                    @livewire('package-tracking-table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
