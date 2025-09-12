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
                            <div class="space-y-1">
                                <p class="text-sm text-gray-500">Available Balance</p>
                                <p class="text-2xl font-bold text-blue-600">UGX {{ number_format($client->available_balance ?? 0, 2) }}</p>
                                <p class="text-sm text-gray-500">Total Balance</p>
                                <p class="text-lg font-semibold text-gray-700">UGX {{ number_format($client->total_balance ?? 0, 2) }}</p>
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
                                                    @elseif($history->transaction_type === 'payment') bg-red-100 text-red-800
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
                                                        
                                                        // Remove "payment" and "service charge" words
                                                        $description = preg_replace('/\bpayment\b/i', '', $description);
                                                        $description = preg_replace('/\bservice\s+charge\b/i', 'Platform Fee', $description);
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
                                                        $description = 'Platform Fee';
                                                    }
                                                @endphp
                                                {{ $description }}
                                                @if($history->notes)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $history->notes }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <span class="@if($history->change_amount > 0) text-green-600 @else text-red-600 @endif">
                                                    {{ $history->getFormattedChangeAmount() }}
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

