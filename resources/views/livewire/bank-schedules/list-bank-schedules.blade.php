<div>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Bank Schedules
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage bank transfer schedules for clients
                    </p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white shadow rounded-lg mb-4">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button wire:click="setActiveTab('pending')" 
                                class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <svg class="-ml-0.5 mr-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pending
                        </button>
                        <button wire:click="setActiveTab('completed')" 
                                class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'completed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <svg class="-ml-0.5 mr-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Completed
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Filament Table -->
            <div class="bg-white shadow rounded-lg">
                {{ $this->table }}
            </div>
        </div>
    </div>
</div>
