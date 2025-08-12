<div class="space-y-6">
    
    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('clients.create') }}" class="bg-[#011478] text-white px-6 py-3 rounded-lg hover:bg-[#011478]/90 transition-colors flex items-center">
                <i class="fas fa-user-plus mr-2"></i>
                Add New Client
            </a>
            <a href="{{ route('clients.index') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                <i class="fas fa-users mr-2"></i>
                View All Clients
            </a>
        </div>
    </div>
    
    <!-- Account Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Account Balance Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Balance</h3>
                <span class="text-xs text-gray-400">Last Update: 2025-06-23 10:00 AM</span>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-[#011478] dark:text-white">
                    {{ number_format($balance, 2) }}
                </span>
                <span class="ml-2 text-sm text-gray-500">UGX</span>
            </div>
        </div>

        <!-- Total Clients Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Clients</h3>
                <span class="text-xs text-gray-400">{{ $currentBranch->name ?? 'All Branches' }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-[#011478] dark:text-white">
                    {{ \App\Models\Client::where('business_id', $business->id)->where('branch_id', $currentBranch->id)->count() }}
                </span>
                <span class="ml-2 text-sm text-gray-500">Clients</span>
            </div>
        </div>

        <!-- Today's Clients Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Clients</h3>
                <span class="text-xs text-gray-400">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ \App\Models\Client::where('business_id', $business->id)->where('branch_id', $currentBranch->id)->whereDate('created_at', today())->count() }}
                </span>
                <span class="ml-2 text-sm text-gray-500">New</span>
            </div>
        </div>

        <!-- Total Transactions Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</h3>
                <span class="text-xs text-gray-400">All Time</span>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-[#011478] dark:text-white">
                    1,234
                </span>
                <span class="ml-2 text-sm text-gray-500">Txns</span>
            </div>
        </div>

    </div>

    <!-- Recent Clients -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Clients</h3>
            <a href="{{ route('clients.index') }}" class="text-[#011478] hover:text-[#011478]/80 text-sm font-medium">View All</a>
        </div>
        
        @livewire('recent-clients-table')
    </div>

</div>


