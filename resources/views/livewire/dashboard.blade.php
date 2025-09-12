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
        
        @php
            $recentTransactions = \App\Models\Transaction::where('business_id', $business->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        @endphp
        
        @if($recentTransactions->count() > 0)
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
                        @foreach($recentTransactions as $transaction)
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

</div>

<script>
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


