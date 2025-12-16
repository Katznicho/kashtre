<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Account Statement') }} - {{ $client->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Client Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $client->name }}</h3>
                            <p class="text-sm text-gray-500">Client ID: {{ $client->client_id }}</p>
                            <p class="text-sm text-gray-500">Phone: {{ $client->phone_number }}</p>
                        </div>
                        <div class="text-right">
                            <div class="space-y-2">
                                @php
                                    // Available Balance = credits - debits (from balance history)
                                    $credits = $client->balanceHistories()->where('transaction_type', 'credit')->sum('change_amount');
                                    $debits = abs($client->balanceHistories()->where('transaction_type', 'debit')->sum('change_amount'));
                                    $availableBalance = $credits - $debits;
                                    
                                    // Suspense Balance = credits - debits in suspense accounts (from MoneyAccount balances)
                                    // This represents money in suspense accounts that hasn't been moved to final accounts yet
                                    $suspenseBalance = $client->suspense_balance ?? 0;
                                    
                                    // Total Balance = Available Balance + Suspense Balance
                                    $totalBalance = $availableBalance + $suspenseBalance;
                                    
                                    // Credit Remaining calculation for credit clients
                                    $creditLimit = $client->max_credit ?? 0;
                                    $amountOwed = $availableBalance < 0 ? abs($availableBalance) : 0;
                                    $creditRemaining = max(0, $creditLimit - $amountOwed);
                                    
                                    $availableBalanceColor = $availableBalance < 0 ? 'text-red-600' : ($availableBalance > 0 ? 'text-green-600' : 'text-gray-700');
                                    $totalBalanceColor = $totalBalance < 0 ? 'text-red-600' : ($totalBalance > 0 ? 'text-green-600' : 'text-gray-700');
                                    $creditRemainingColor = $creditRemaining > 0 ? 'text-green-600' : 'text-red-600';
                                @endphp
                                
                                @if($client->is_credit_eligible)
                                    {{-- Credit Client: Show Total Balance, Available Balance, and Credit Limit --}}
                                    <div>
                                        <p class="text-sm text-gray-500">Total Balance</p>
                                        <p class="text-lg font-semibold {{ $totalBalanceColor }}">
                                            UGX {{ number_format($totalBalance, 2) }}
                                        </p>
                                        @if($totalBalance < 0)
                                            <p class="text-xs text-red-500">(Amount Owed)</p>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Available Balance</p>
                                        <p class="text-lg font-semibold {{ $availableBalanceColor }}">
                                            UGX {{ number_format($availableBalance, 2) }}
                                        </p>
                                        @if($availableBalance < 0)
                                            <p class="text-xs text-red-500">(Amount Owed)</p>
                                        @elseif($availableBalance > 0)
                                            <p class="text-xs text-green-500">(Credit Available)</p>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Credit Limit</p>
                                        <p class="text-lg font-semibold text-gray-700">
                                            UGX {{ number_format($creditLimit, 2) }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Credit Remaining</p>
                                        <p class="text-lg font-semibold {{ $creditRemainingColor }}">
                                            UGX {{ number_format($creditRemaining, 2) }}
                                        </p>
                                        @if($creditRemaining <= 0)
                                            <p class="text-xs text-red-500">(Credit Limit Exceeded)</p>
                                        @elseif($amountOwed > 0)
                                            <p class="text-xs text-gray-500">({{ number_format($amountOwed, 2) }} used of {{ number_format($creditLimit, 2) }})</p>
                                        @else
                                            <p class="text-xs text-green-500">(No credit used)</p>
                                        @endif
                                    </div>
                                @else
                                    {{-- Non-Credit Client: Show Total Balance and Available Balance --}}
                                    <div>
                                        <p class="text-sm text-gray-500">Total Balance</p>
                                        <p class="text-lg font-semibold {{ $totalBalanceColor }}">
                                            UGX {{ number_format($totalBalance, 2) }}
                                        </p>
                                        @if($totalBalance < 0)
                                            <p class="text-xs text-red-500">(Amount Owed)</p>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Available Balance</p>
                                        <p class="text-lg font-semibold {{ $availableBalanceColor }}">
                                            UGX {{ number_format($availableBalance, 2) }}
                                        </p>
                                        @if($availableBalance < 0)
                                            <p class="text-xs text-red-500">(Amount Owed)</p>
                                        @elseif($availableBalance > 0)
                                            <p class="text-xs text-green-500">(Credit Available)</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance Statement Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Client Account Statement</h3>
                        <div class="flex space-x-2">
                            @php
                                $calculatedBalance = $credits - $debits;
                                $isInitiator = \App\Models\CreditLimitApprovalApprover::where('business_id', auth()->user()->business_id)
                                    ->where('approver_id', auth()->user()->id)
                                    ->where('approval_level', 'initiator')
                                    ->exists();
                            @endphp
                            @if($client->is_credit_eligible && in_array('Manage Credit Limits', (array) (auth()->user()->permissions ?? [])) && $isInitiator)
                                <a href="{{ route('credit-limit-requests.create', ['entity_type' => 'client', 'entity_id' => $client->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-block">
                                    Request Credit Limit Change
                                </a>
                            @endif
                            @php
                                // Check if there are any delivered services with outstanding amounts
                                // Only show button if money has moved from suspense to final accounts (services delivered)
                                $hasDeliveredServices = false;
                                if ($client->is_credit_eligible && $calculatedBalance < 0) {
                                    $invoicesWithDeliveredServices = \App\Models\BalanceHistory::where('client_id', $client->id)
                                        ->where('transaction_type', 'debit')
                                        ->whereNotNull('invoice_id')
                                        ->with(['invoice'])
                                        ->get()
                                        ->filter(function ($entry) {
                                            if (!$entry->invoice || $entry->invoice->balance_due <= 0) {
                                                return false;
                                            }
                                            
                                            // Check if money has moved from suspense to final accounts (services delivered)
                                            return \App\Models\MoneyTransfer::where('invoice_id', $entry->invoice_id)
                                                ->where('transfer_type', 'suspense_to_final')
                                                ->where('money_moved_to_final_account', true)
                                                ->exists();
                                        });
                                    
                                    $hasDeliveredServices = $invoicesWithDeliveredServices->count() > 0;
                                }
                            @endphp
                            @if($client->is_credit_eligible && $calculatedBalance < 0 && $hasDeliveredServices && in_array('Process Pay Back', (array) (auth()->user()->permissions ?? [])))
                                <a href="{{ route('balance-statement.pay-back.show', $client->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-block">
                                    Pay Out Standing Amount
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($balanceHistories->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($balanceHistories as $history)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $history->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    @if($history->transaction_type === 'credit') bg-green-100 text-green-800
                                                    @elseif($history->transaction_type === 'debit') bg-red-100 text-red-800
                                                    @elseif($history->transaction_type === 'payment') bg-orange-100 text-orange-800
                                                    @elseif($history->transaction_type === 'package') bg-blue-100 text-blue-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($history->transaction_type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                @php
                                                    $description = $history->description;
                                                    
                                                    // Simplify descriptions for statements
                                                    if (str_contains($description, 'Payment received via mobile_money')) {
                                                        $description = 'Mobile Money Payment';
                                                    } elseif (str_contains($description, 'Payment received for invoice')) {
                                                        $description = 'Invoice Payment';
                                                    } elseif (str_contains($description, 'Payment received via')) {
                                                        $description = str_replace('Payment received via ', '', $description);
                                                        $description = ucwords(str_replace('_', ' ', $description)) . ' Payment';
                                                    } elseif (str_contains($description, 'Payment for:')) {
                                                        // Extract item names from "Payment for: Item1, Item2, Item3"
                                                        $description = str_replace('Payment for: ', '', $description);
                                                        
                                                        // Remove quantities and types (e.g., "(x2) - good")
                                                        $description = preg_replace('/\s*\(x\d+\)\s*-\s*\w+/', '', $description);
                                                        
                                                        // Remove client and business info (e.g., "for Tonny Musis (ID: DEH2123C) at Demo Hospital")
                                                        $description = preg_replace('/\s+for\s+[^,]+(?:\([^)]+\))?(?:\s+at\s+[^-]+)?/', '', $description);
                                                        
                                                        // Remove invoice reference (e.g., "- Invoice: P2025090013")
                                                        $description = preg_replace('/\s*-\s*Invoice:\s*[A-Z0-9]+/', '', $description);
                                                        
                                                        // Remove "payment" word and change service charge to Service Fee
                                                        $description = preg_replace('/\bpayment\b/i', '', $description);
                                                        $description = preg_replace('/\bservice\s+charge\b/i', 'Service Fee', $description);
                                                        $description = preg_replace('/\breceived\s+via\b/i', 'via', $description);
                                                        $description = preg_replace('/\bcompleted\s*-\s*Item\s+purchased:\s*/i', '', $description);
                                                        
                                                        // Clean up any remaining extra spaces and commas
                                                        $description = preg_replace('/\s*,\s*$/', '', $description);
                                                        $description = preg_replace('/\s+/', ' ', trim($description));
                                                        
                                                        // If description is too long, truncate it
                                                        if (strlen($description) > 50) {
                                                            $description = substr($description, 0, 47) . '...';
                                                        }
                                                    } elseif (str_contains($description, 'Service Charge')) {
                                                        $description = 'Service Fee';
                                                    }
                                                @endphp
                                                {{ $description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <span class="@if($history->transaction_type === 'package') text-blue-600 @elseif($history->change_amount > 0) text-green-600 @else text-red-600 @endif">
                                                    @if($history->transaction_type === 'debit')
                                                        UGX {{ number_format(abs($history->change_amount), 2) }}
                                                    @else
                                                        {{ $history->getFormattedChangeAmount() }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $history->reference_number ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $balanceHistories->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No balance statement found for this client.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


</x-app-layout>

