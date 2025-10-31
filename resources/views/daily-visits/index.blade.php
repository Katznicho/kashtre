<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Daily Visits Record</h1>
                        @if($business->id == 1)
                            <p class="text-gray-600 mt-2">Kashtre - All Businesses</p>
                            <p class="text-sm text-blue-600 mt-1">Showing visits from: <strong>All Businesses</strong></p>
                        @else
                            <p class="text-gray-600 mt-2">{{ $business->name }} - {{ $currentBranch->name }}</p>
                            <p class="text-sm text-blue-600 mt-1">Showing visits for: <strong>{{ $selectedBranch->name }}</strong></p>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('clients.index') }}" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Clients
                        </a>
                    </div>
                </div>

                <!-- Filament handles filters within the table -->

                <!-- Filament Table for Daily Visits -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Daily Visits</h2>
                    </div>
                    <div class="p-6">
                        <livewire:daily-visits.list-daily-visits />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

