<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('Invoice Details') }} - {{ $invoice->invoice_number }}
                        </h2>
                        <div class="flex space-x-3">
                            <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                                Back to Invoices
                            </a>
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                Print Invoice
                            </a>
                            <button onclick="generateQuotation({{ $invoice->id }})" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                                Generate Quotation
                            </button>
                            @if($invoice->quotations->count() > 0)
                                @php
                                    $latestQuotation = $invoice->quotations->sortByDesc('created_at')->first();
                                @endphp
                                <a href="{{ route('quotations.print', $latestQuotation) }}" target="_blank" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors">
                                    Print Quotation
                                </a>
                            @else
                                <button disabled class="px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed" title="No quotations generated yet. Use 'Generate Quotation' button first.">
                                    Print Quotation
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Invoice Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Invoice Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $invoice->invoice_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_badge }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->payment_status_badge }}">
                                            {{ ucfirst($invoice->payment_status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Created</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($invoice->confirmed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Confirmed</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->confirmed_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Client Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Client Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->client_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->client_phone }}</dd>
                                </div>
                                @if($invoice->payment_phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Phone</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->payment_phone }}</dd>
                                </div>
                                @endif
                                @if($invoice->visit_id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Visit ID</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->visit_id }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if($invoice->items && is_array($invoice->items))
                                    @foreach($invoice->items as $item)
                                    @php
                                        // Get the actual Item model to use display_name attribute
                                        $itemModel = \App\Models\Item::find($item['id'] ?? $item['item_id'] ?? null);
                                        $displayName = $itemModel ? $itemModel->display_name : ($item['name'] ?? 'N/A');
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $displayName }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            UGX {{ number_format($item['price'] ?? 0, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item['quantity'] ?? 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            UGX {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No items found
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Financial Summary -->
                        <div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Subtotal 1</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->subtotal, 2) }}</dd>
                                </div>
                                @if($invoice->package_adjustment != 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Package Adjustment</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->package_adjustment, 2) }}</dd>
                                </div>
                                @endif
                                @if($invoice->account_balance_adjustment != 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Account Balance(A/c) Adjustment</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->account_balance_adjustment, 2) }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Subtotal 2</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format(max(0, $invoice->subtotal - ($invoice->package_adjustment ?? 0) - ($invoice->account_balance_adjustment ?? 0)), 2) }}</dd>
                                </div>
                                @if($invoice->service_charge > 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Service Charge</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->service_charge, 2) }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between border-t pt-3">
                                    <dt class="text-lg font-bold text-gray-900">Total</dt>
                                    <dd class="text-lg font-bold text-gray-900">UGX {{ number_format($invoice->total_amount, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Amount Paid</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->amount_paid, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Balance Due</dt>
                                    <dd class="text-sm font-semibold {{ $invoice->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        UGX {{ number_format($invoice->balance_due, 2) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Payment Methods -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Payment Methods</h4>
                            @if($invoice->payment_methods && is_array($invoice->payment_methods))
                                @php
                                    // Check if payment_methods has amounts
                                    $hasAmounts = false;
                                    foreach($invoice->payment_methods as $method) {
                                        if (is_array($method) && isset($method['method']) && isset($method['amount'])) {
                                            $hasAmounts = true;
                                            break;
                                        }
                                    }
                                @endphp
                                
                                @if($hasAmounts)
                                    <div class="space-y-2">
                                        @foreach($invoice->payment_methods as $methodData)
                                            @if(is_array($methodData) && isset($methodData['method']) && isset($methodData['amount']))
                                                <div class="flex items-center justify-between">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ ucwords(str_replace('_', ' ', $methodData['method'])) }}
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        UGX {{ number_format($methodData['amount'], 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Fallback for old format --}}
                                    <div class="space-y-2">
                                        @foreach($invoice->payment_methods as $method)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucwords(str_replace('_', ' ', $method)) }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @if($invoice->amount_paid > 0)
                                        <div class="text-sm text-gray-600 mt-2">
                                            Total Paid: UGX {{ number_format($invoice->amount_paid, 2) }}
                                        </div>
                                    @endif
                                @endif
                            @else
                                <span class="text-sm text-gray-500">No payment methods specified</span>
                            @endif

                            @if($invoice->notes)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Notes</h4>
                                <p class="text-sm text-gray-900">{{ $invoice->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business & Branch Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Business</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->business->name ?? 'N/A' }}</dd>
                        </div>
                        @if($invoice->branch)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Branch</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->branch->name ?? 'N/A' }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->createdBy->name ?? 'N/A' }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Transactions & Reinitiation Section -->
            @php
                $failedTransactions = \App\Models\Transaction::where('invoice_id', $invoice->id)
                    ->where('status', 'failed')
                    ->where('method', 'mobile_money')
                    ->where('provider', 'yo')
                    ->get();
            @endphp
            
            @if($failedTransactions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Failed Transactions</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-red-800">
                                This invoice has {{ $failedTransactions->count() }} failed mobile money transaction(s). 
                                You can reinitiate payment for these transactions.
                            </p>
                        </div>
                    </div>
                    
                    @foreach($failedTransactions as $transaction)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Transaction #{{ $transaction->id }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Amount: UGX {{ number_format($transaction->amount, 2) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Reference: {{ $transaction->external_reference ?? $transaction->reference ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Failed: {{ $transaction->updated_at->format('M d, Y H:i:s') }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="reinitiateTransaction({{ $transaction->id }})" 
                                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                    Reinitiate Payment
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button onclick="reinitiateAllFailedTransactions({{ $invoice->id }})" 
                                class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition-colors">
                            Reinitiate All Failed Transactions
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Reinitiate a single failed transaction
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

        // Reinitiate all failed transactions for an invoice
        async function reinitiateAllFailedTransactions(invoiceId) {
            try {
                const response = await fetch('/invoices/reinitiate-failed-invoice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'All Payments Reinitiated!',
                        text: data.message || 'All failed payments have been reinitiated successfully.'
                    }).then(() => {
                        // Reload the page to show updated status
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to reinitiate payments.'
                    });
                }
            } catch (error) {
                console.error('Error reinitiating all transactions:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while reinitiating the payments.'
                });
            }
        }

        async function generateQuotation(invoiceId) {
            try {
                console.log('Generating quotation for invoice:', invoiceId);
                
                // Check if CSRF token exists
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page and try again.'
                    });
                    return;
                }
                
                // Show loading state
                Swal.fire({
                    title: 'Generating Quotation...',
                    html: `
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
                            </div>
                            <p class="text-sm text-gray-600">Please wait while we generate your quotation...</p>
                        </div>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                console.log('Making API call to:', `/invoices/${invoiceId}/generate-quotation`);
                
                // Make API call to generate quotation
                const response = await fetch(`/invoices/${invoiceId}/generate-quotation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Quotation Generated!',
                        html: `
                            <div class="text-center">
                                <p class="mb-2">Quotation has been generated successfully.</p>
                                <p class="text-sm text-gray-600">Quotation Number: <strong>${data.quotation_number}</strong></p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'View Quotation',
                        cancelButtonText: 'Print Quotation',
                        showDenyButton: true,
                        denyButtonText: 'Stay Here'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // View quotation
                            window.open(`/quotations/${data.quotation_id}`, '_blank');
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Print quotation
                            window.open(`/quotations/${data.quotation_id}/print`, '_blank');
                        }
                        // If "Stay Here" is clicked, do nothing
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to generate quotation'
                    });
                }
            } catch (error) {
                console.error('Error generating quotation:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: `An error occurred while generating the quotation: ${error.message}`
                });
            }
        }
    </script>
</x-app-layout>
