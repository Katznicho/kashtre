<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Balance Statement') }} - {{ $client->name }}
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

            <!-- Financial Status Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Status Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Available Balance -->
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">UGX {{ number_format($client->available_balance ?? 0, 2) }}</div>
                            <div class="text-sm text-gray-600 mt-1">Available Balance</div>
                            <div class="text-xs text-gray-500 mt-1">Money you can use</div>
                        </div>
                        
                        <!-- Total Balance -->
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">UGX {{ number_format($client->total_balance ?? 0, 2) }}</div>
                            <div class="text-sm text-gray-600 mt-1">Total Balance</div>
                            <div class="text-xs text-gray-500 mt-1">Available + Suspense</div>
                        </div>
                        

                    </div>
                </div>
            </div>

            <!-- Balance Statement Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
                            <p class="text-sm text-gray-500">Complete record of all financial transactions</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="exportStatement()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors text-sm">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>

                    @if($balanceHistories->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Date & Time
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Transaction Type
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Description
                                            </div>
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                Amount
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.122 2.122"></path>
                                                </svg>
                                                Reference
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                User
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="balance-statement-table" class="bg-white divide-y divide-gray-200">
                                    @foreach($balanceHistories as $history)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $history->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    @if($history->change_amount > 0) bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    @if($history->change_amount > 0)
                                                        Credit
                                                    @else
                                                        Debit
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $history->description }}
                                                @if($history->notes)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $history->notes }}</p>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-amount="{{ $history->change_amount > 0 ? '+' : '' }}{{ number_format($history->change_amount, 2) }}">
                                                <span class="@if($history->change_amount > 0) text-green-600 @else text-red-600 @endif">
                                                    {{ $history->change_amount > 0 ? '+' : '' }}UGX {{ number_format(abs($history->change_amount), 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($history->invoice_id && $history->invoice)
                                                    Invoice #{{ $history->invoice->invoice_number }}
                                                @elseif($history->reference_number)
                                                    {{ $history->reference_number }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $history->user ? $history->user->name : 'System' }}
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



    <script>




        function exportStatement() {
            // Get the current date for filename
            const now = new Date();
            const dateStr = now.toISOString().split('T')[0];
            const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
            
            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Add header
            csvContent += "Date & Time,Transaction Type,Description,Amount,Reference,User\n";
            
            // Get all table rows
            const rows = document.querySelectorAll('#balance-statement-table tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [];
                
                cells.forEach((cell, index) => {
                    let cellText = cell.textContent.trim();
                    
                    // Clean up the data
                    if (index === 0) { // Date
                        rowData.push(cellText);
                    } else if (index === 1) { // Transaction Type
                        rowData.push(cellText);
                    } else if (index === 2) { // Description
                        rowData.push(cellText.replace(/"/g, '""')); // Escape quotes
                    } else if (index === 3) { // Amount (from change_amount)
                        // Get the amount from the data attribute or calculate it
                        const amountCell = row.querySelector('[data-amount]');
                        if (amountCell) {
                            rowData.push(amountCell.dataset.amount);
                        } else {
                            rowData.push(cellText);
                        }
                    } else if (index === 4) { // Reference
                        rowData.push(cellText);
                    } else if (index === 5) { // User
                        rowData.push(cellText);
                    }
                });
                
                csvContent += rowData.map(field => `"${field}"`).join(',') + '\n';
            });
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `client_statement_${dateStr}_${timeStr}.csv`);
            document.body.appendChild(link);
            
            // Trigger download
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Export Successful',
                text: 'Balance statement has been exported to CSV',
                timer: 2000,
                showConfirmButton: false
            });
        }
    </script>
</x-app-layout>

