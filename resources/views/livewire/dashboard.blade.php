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
                    {{ \App\Models\Transaction::where('business_id', $business->id)->count() }}
                </span>
                <span class="ml-2 text-sm text-gray-500">Txns</span>
            </div>
        </div>

        <!-- Today's Transactions Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Transactions</h3>
                <span class="text-xs text-gray-400">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ \App\Models\Transaction::where('business_id', $business->id)->whereDate('created_at', today())->count() }}
                </span>
                <span class="ml-2 text-sm text-gray-500">Today</span>
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

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
            <a href="{{ route('transactions.index') }}" class="text-[#011478] hover:text-[#011478]/80 text-sm font-medium">View All</a>
        </div>
        
        <!-- Transaction Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTransactionTab('all')" id="tab-all" class="transaction-tab py-2 px-1 border-b-2 border-[#011478] font-medium text-sm text-[#011478]">
                        All
                        <span class="ml-2 bg-[#011478] text-white text-xs rounded-full px-2 py-1">
                            {{ \App\Models\Transaction::where('business_id', $business->id)->count() }}
                        </span>
                    </button>
                    <button onclick="showTransactionTab('pending')" id="tab-pending" class="transaction-tab py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Pending
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs rounded-full px-2 py-1">
                            {{ \App\Models\Transaction::where('business_id', $business->id)->where('status', 'pending')->count() }}
                        </span>
                    </button>
                    <button onclick="showTransactionTab('completed')" id="tab-completed" class="transaction-tab py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Completed
                        <span class="ml-2 bg-green-100 text-green-800 text-xs rounded-full px-2 py-1">
                            {{ \App\Models\Transaction::where('business_id', $business->id)->where('status', 'completed')->count() }}
                        </span>
                    </button>
                    <button onclick="showTransactionTab('failed')" id="tab-failed" class="transaction-tab py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Failed
                        <span class="ml-2 bg-red-100 text-red-800 text-xs rounded-full px-2 py-1">
                            {{ \App\Models\Transaction::where('business_id', $business->id)->where('status', 'failed')->count() }}
                        </span>
                    </button>
                </nav>
            </div>
        </div>
        
        @php
            $allTransactions = \App\Models\Transaction::where('business_id', $business->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $pendingTransactions = \App\Models\Transaction::where('business_id', $business->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $completedTransactions = \App\Models\Transaction::where('business_id', $business->id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $failedTransactions = \App\Models\Transaction::where('business_id', $business->id)
                ->where('status', 'failed')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        @endphp
        
        <!-- All Transactions Tab Content -->
        <div id="transactions-all" class="transaction-content">
            @if($allTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-blue-600">
                                    @if($transaction->invoice_id)
                                        <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="hover:text-blue-800">
                                            {{ $transaction->reference }}
                                        </a>
                                    @else
                                        <span class="text-gray-600">{{ $transaction->reference }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900">
                                    <div class="truncate max-w-xs" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    UGX {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $transaction->method ?? $transaction->provider)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                        @if($transaction->status === 'failed' && $transaction->method === 'mobile_money' && $transaction->provider === 'yo')
                                            <button onclick="reinitiateTransaction({{ $transaction->id }})" 
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800 hover:bg-orange-200 transition-colors"
                                                    title="Reinitiate Payment">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                Retry
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions found</h3>
                    <p class="mt-1 text-sm text-gray-500">No transactions have been recorded yet.</p>
                </div>
            @endif
        </div>

        <!-- Pending Transactions Tab Content -->
        <div id="transactions-pending" class="transaction-content hidden">
            @if($pendingTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendingTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-blue-600">
                                    @if($transaction->invoice_id)
                                        <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="hover:text-blue-800">
                                            {{ $transaction->reference }}
                                        </a>
                                    @else
                                        <span class="text-gray-600">{{ $transaction->reference }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900">
                                    <div class="truncate max-w-xs" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    UGX {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $transaction->method ?? $transaction->provider)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending transactions</h3>
                    <p class="mt-1 text-sm text-gray-500">All transactions are processed.</p>
                </div>
            @endif
        </div>

        <!-- Completed Transactions Tab Content -->
        <div id="transactions-completed" class="transaction-content hidden">
            @if($completedTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($completedTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-blue-600">
                                    @if($transaction->invoice_id)
                                        <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="hover:text-blue-800">
                                            {{ $transaction->reference }}
                                        </a>
                                    @else
                                        <span class="text-gray-600">{{ $transaction->reference }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900">
                                    <div class="truncate max-w-xs" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    UGX {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $transaction->method ?? $transaction->provider)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No completed transactions</h3>
                    <p class="mt-1 text-sm text-gray-500">No transactions have been completed yet.</p>
                </div>
            @endif
        </div>

        <!-- Failed Transactions Tab Content -->
        <div id="transactions-failed" class="transaction-content hidden">
            @if($failedTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($failedTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-blue-600">
                                    @if($transaction->invoice_id)
                                        <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="hover:text-blue-800">
                                            {{ $transaction->reference }}
                                        </a>
                                    @else
                                        <span class="text-gray-600">{{ $transaction->reference }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900">
                                    <div class="truncate max-w-xs" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    UGX {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $transaction->method ?? $transaction->provider)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                        @if($transaction->method === 'mobile_money' && $transaction->provider === 'yo')
                                            <button onclick="reinitiateTransaction({{ $transaction->id }})" 
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800 hover:bg-orange-200 transition-colors"
                                                    title="Reinitiate Payment">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                Retry
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No failed transactions</h3>
                    <p class="mt-1 text-sm text-gray-500">All transactions are processing successfully.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    // Tab switching functionality
    function showTransactionTab(tabName) {
        // Hide all tab contents
        const allContents = document.querySelectorAll('.transaction-content');
        allContents.forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active styling from all tabs
        const allTabs = document.querySelectorAll('.transaction-tab');
        allTabs.forEach(tab => {
            tab.classList.remove('border-[#011478]', 'text-[#011478]');
            tab.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        const selectedContent = document.getElementById(`transactions-${tabName}`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }

        // Add active styling to selected tab
        const selectedTab = document.getElementById(`tab-${tabName}`);
        if (selectedTab) {
            selectedTab.classList.remove('border-transparent', 'text-gray-500');
            selectedTab.classList.add('border-[#011478]', 'text-[#011478]');
        }
    }

    // Reinitiate a single failed transaction from dashboard
    async function reinitiateTransaction(transactionId) {
        try {
            const response = await fetch('/invoices/reinitiate-failed-transaction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    transaction_id: transactionId
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Reinitiated!',
                    text: data.message || 'Payment has been reinitiated successfully.'
                }).then(() => {
                    // Reload the page to show updated status
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to reinitiate payment.'
                });
            }
        } catch (error) {
            console.error('Error reinitiating transaction:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while reinitiating the payment.'
            });
        }
    }
</script>


