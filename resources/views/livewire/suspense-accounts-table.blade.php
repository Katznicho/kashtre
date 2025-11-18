<div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-table text-blue-500 mr-2"></i>
                Suspense Accounts Records
            </h3>
            <p class="text-gray-600 mt-2">Money movements with search and filter capabilities</p>
        </div>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="setActiveTab('package')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'package' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-box mr-2"></i>
                    Package Suspense
                </button>
                <button wire:click="setActiveTab('general')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'general' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-clock mr-2"></i>
                    General Suspense
                </button>
                <button wire:click="setActiveTab('kashtre')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'kashtre' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-hand-holding-usd mr-2"></i>
                    Kashtre Suspense
                </button>
                <button wire:click="setActiveTab('withdrawal')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'withdrawal' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Withdrawal Suspense
                </button>
            </nav>
        </div>
        
        <div class="p-6">
            {{ $this->table }}
        </div>
    </div>
</div>
