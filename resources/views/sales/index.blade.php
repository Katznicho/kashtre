<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Master Sales</h1>
                    <p class="mt-1 text-sm text-gray-600">Track all completed and partially completed transactions across the network.</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-4">
                    @livewire('sales.list-sales')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

