<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Refunds</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Track refund requests raised when service items are marked as not done.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-4">
                    @livewire('refunds.list-refunds')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


