<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Daily Visits</h1>
                <a href="{{ route('clients.index') }}" class="text-sm px-4 py-2 rounded-md bg-gray-700 text-white">Back to Clients</a>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-3 border-b flex items-center justify-between gap-4 flex-wrap">
                    @if($business->id == 1)
                        <p class="text-sm text-gray-600">Kashtre - All Businesses</p>
                    @else
                        <p class="text-sm text-gray-600">{{ $business->name }} — {{ $selectedBranch->name }}</p>
                    @endif
                </div>
                <div class="p-4">
                    @livewire('daily-visits.list-daily-visits')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

