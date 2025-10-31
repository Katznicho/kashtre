<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Finance • Withdrawal Requests
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div>
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button x-data="{ tab: $store.tabs }" @click="$store.tabs = 'contractor'" :class="$store.tabs === 'contractor' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Contractor
                                </button>
                                <button x-data="{ tab: $store.tabs }" @click="$store.tabs = 'business'" :class="$store.tabs === 'business' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Business
                                </button>
                            </nav>
                        </div>

                        <div x-data="{ tabs: 'contractor' }" x-init="$store.tabs = 'contractor'">
                            <div x-show="$store.tabs === 'contractor'">
                                <livewire:contractor-withdrawals.list-all-contractor-withdrawal-requests />
                            </div>
                            <div x-show="$store.tabs === 'business'" x-cloak>
                                <livewire:withdrawal-requests.list-withdrawal-requests />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


