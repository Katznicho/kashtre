<x-app-layout>
    <x-slot name="header">
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
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item['name'] ?? 'N/A' }}
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
                                    <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
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
                                @if($invoice->service_charge > 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Service Charge</dt>
                                    <dd class="text-sm text-gray-900">UGX {{ number_format($invoice->service_charge, 2) }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between border-t pt-3">
                                    <dt class="text-lg font-bold text-gray-900">Total Amount</dt>
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
        </div>
    </div>
</x-app-layout>
