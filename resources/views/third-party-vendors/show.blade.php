<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Third Party Vendor Details') }} - {{ $vendor['name'] }}
            </h2>
            <a href="{{ route('third-party-vendors.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Vendor Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Vendor Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vendor Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $vendor['name'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <code class="bg-gray-100 px-2 py-1 rounded">{{ $vendor['code'] }}</code>
                                    </dd>
                                </div>
                                @if($vendor['email'])
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $vendor['email'] }}</dd>
                                </div>
                                @endif
                                @if($vendor['phone'])
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $vendor['phone'] }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        @if($vendor['is_active'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Connected At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($vendor['connected_at'])->format('M d, Y H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>
                            <dl class="space-y-3">
                                @if($thirdPartyPayer)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                                    <dd class="mt-1 text-lg font-semibold {{ $currentBalance < 0 ? 'text-red-600' : ($currentBalance > 0 ? 'text-green-600' : 'text-gray-900') }}">
                                        UGX {{ number_format(abs($currentBalance), 2) }}
                                        @if($currentBalance < 0)
                                            <span class="text-xs text-red-500">(Amount Owed)</span>
                                        @elseif($currentBalance > 0)
                                            <span class="text-xs text-green-500">(Credit Available)</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Credits</dt>
                                    <dd class="mt-1 text-lg font-semibold text-green-600">
                                        UGX {{ number_format($totalCredits, 2) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Debits</dt>
                                    <dd class="mt-1 text-lg font-semibold text-red-600">
                                        UGX {{ number_format($totalDebits, 2) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Credit Limit</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        UGX {{ number_format($thirdPartyPayer->credit_limit ?? 0, 2) }}
                                    </dd>
                                </div>
                                @else
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        No third-party payer account found for this vendor. Balance history will appear here once invoices are created with this vendor.
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            @if($thirdPartyPayer)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button onclick="showTab('transactions')" id="tab-transactions" class="tab-button active w-1/2 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors">
                            <span class="border-b-2 border-blue-500 pb-4 px-1 text-blue-600">Transactions</span>
                        </button>
                        <button onclick="showTab('invoices')" id="tab-invoices" class="tab-button w-1/2 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors">
                            <span class="border-b-2 border-transparent pb-4 px-1 text-gray-500 hover:text-gray-700">Invoices</span>
                        </button>
                    </nav>
                </div>

                <!-- Transactions Tab Content -->
                <div id="content-transactions" class="tab-content p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                            <p class="text-sm text-gray-500">Showing last 10 transactions</p>
                        </div>
                        <a href="{{ route('third-party-vendors.balance-statement', $vendor['id']) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            View Full Statement
                        </a>
                    </div>
                    
                    @if($balanceHistories->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($balanceHistories as $history)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $history->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $history->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($history->client)
                                            <span class="font-medium">{{ $history->client->name }}</span>
                                            @if($history->client->client_id)
                                                <br><span class="text-xs text-gray-500">ID: {{ $history->client->client_id }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($history->invoice)
                                            <a href="{{ route('invoices.show', $history->invoice->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                                {{ $history->invoice->invoice_number ?? 'N/A' }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $history->transaction_type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($history->transaction_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $history->transaction_type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $history->transaction_type === 'credit' ? '+' : '-' }}{{ number_format(abs($history->change_amount), 2) }} UGX
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($history->new_balance, 2) }} UGX
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $history->payment_method ? ucwords(str_replace('_', ' ', $history->payment_method)) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($history->payment_status)
                                        <span class="px-2 py-1 text-xs rounded-full {{ $history->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($history->payment_status === 'pending_payment' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $history->payment_status)) }}
                                        </span>
                                        @else
                                        <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('third-party-vendors.balance-statement', $vendor['id']) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            View all transactions →
                        </a>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">No transactions found. Balance history entries will appear here when invoices are created with this vendor.</p>
                    </div>
                    @endif
                </div>

                <!-- Invoices Tab Content -->
                <div id="content-invoices" class="tab-content p-6 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Invoices</h3>
                            <p class="text-sm text-gray-500">All invoices for this vendor</p>
                        </div>
                    </div>
                    
                    @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance Due</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $invoice['invoice_number'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $invoice['client_name'] }}</div>
                                        @if($invoice['client_id'])
                                            <div class="text-xs text-gray-500">ID: {{ $invoice['client_id'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $invoice['business_name'] ?? 'N/A' }}</div>
                                        @if($invoice['branch_name'])
                                            <div class="text-xs text-gray-500">{{ $invoice['branch_name'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        UGX {{ number_format($invoice['total_amount'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        UGX {{ number_format($invoice['amount_paid'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $invoice['balance_due'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        UGX {{ number_format($invoice['balance_due'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $statusColors = [
                                                'paid' => 'bg-green-100 text-green-800',
                                                'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                                'partial' => 'bg-blue-100 text-blue-800',
                                            ];
                                            $paymentStatus = $invoice['payment_status'] ?? 'pending_payment';
                                            $statusColor = $statusColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($invoice['created_at'])->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('invoices.show', $invoice['id']) }}" class="text-blue-600 hover:text-blue-900">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                        <p class="mt-1 text-sm text-gray-500">Invoices will appear here when clients make purchases with this vendor's insurance.</p>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500">No third-party payer account found for this vendor. Balance history will appear here once invoices are created with this vendor.</p>
                    </div>
                </div>
            </div>
            @endif

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        const span = button.querySelector('span');
        span.classList.remove('border-blue-500', 'text-blue-600');
        span.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeButton = document.getElementById('tab-' + tabName);
    const activeSpan = activeButton.querySelector('span');
    activeSpan.classList.remove('border-transparent', 'text-gray-500');
    activeSpan.classList.add('border-blue-500', 'text-blue-600');
}
</script>

<style>
.tab-button {
    cursor: pointer;
}
.tab-button span {
    transition: all 0.2s;
}
.tab-button.active span {
    border-bottom-color: #3b82f6;
    color: #3b82f6;
}
</style>
        </div>
    </div>
</x-app-layout>
