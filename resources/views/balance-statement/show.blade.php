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
                                <p class="text-sm text-gray-500">Account Balance</p>
                                @php
                                    $clientBalance = $client->balance ?? 0;
                                    $balanceColor = $clientBalance < 0 ? 'text-red-600' : ($clientBalance > 0 ? 'text-green-600' : 'text-gray-700');
                                @endphp
                                <p class="text-2xl font-bold {{ $balanceColor }}">
                                    UGX {{ number_format($clientBalance, 2) }}
                                </p>
                                @if($clientBalance < 0)
                                    <p class="text-xs text-red-500">(Amount Owed)</p>
                                @elseif($clientBalance > 0)
                                    <p class="text-xs text-green-500">(Credit Available)</p>
                                @endif
                                @if($client->is_credit_eligible)
                                    <p class="text-sm text-gray-500 mt-2">Credit Limit</p>
                                    <p class="text-lg font-semibold text-gray-700">
                                        UGX {{ number_format($client->max_credit ?? 0, 2) }}
                                    </p>
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
                        @if($client->is_credit_eligible && $client->balance < 0)
                            <button onclick="showPayBackModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Pay Back Outstanding Amount
                            </button>
                        @endif
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

    <!-- Pay Back Modal -->
    <div id="payBackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Pay Back Outstanding Amount</h3>
                    <button onclick="closePayBackModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    <div id="ppEntriesLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading outstanding items...</p>
                    </div>
                    
                    <div id="ppEntriesContainer" class="hidden">
                        <p class="text-sm text-gray-600 mb-4">Select items to pay. Service charges are listed first, followed by other items (oldest to newest).</p>
                        
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="selectAllPP" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Select All</span>
                                </label>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total Selected:</p>
                                <p id="totalSelectedAmount" class="text-lg font-semibold text-gray-900">UGX 0.00</p>
                            </div>
                        </div>
                        
                        <div id="ppEntriesList" class="space-y-2">
                            <!-- PP entries will be loaded here -->
                        </div>
                    </div>
                    
                    <div id="noPPEntries" class="hidden text-center py-8">
                        <p class="text-gray-500">No outstanding items found.</p>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select id="paymentMethod" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="closePayBackModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button onclick="processPayBack()" id="processPayBackBtn" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Process Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ppEntries = [];
        let selectedEntries = new Set();

        function showPayBackModal() {
            document.getElementById('payBackModal').classList.remove('hidden');
            loadPPEntries();
        }

        function closePayBackModal() {
            document.getElementById('payBackModal').classList.add('hidden');
            selectedEntries.clear();
            document.getElementById('selectAllPP').checked = false;
            updateTotalSelected();
        }

        function loadPPEntries() {
            document.getElementById('ppEntriesLoading').classList.remove('hidden');
            document.getElementById('ppEntriesContainer').classList.add('hidden');
            document.getElementById('noPPEntries').classList.add('hidden');
            
            fetch(`/balance-statement/{{ $client->id }}/pp-entries`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ppEntriesLoading').classList.add('hidden');
                    
                    if (data.success && data.entries.length > 0) {
                        ppEntries = data.entries;
                        renderPPEntries();
                        document.getElementById('ppEntriesContainer').classList.remove('hidden');
                    } else {
                        document.getElementById('noPPEntries').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading PP entries:', error);
                    document.getElementById('ppEntriesLoading').classList.add('hidden');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load outstanding items. Please try again.'
                    });
                });
        }

        function renderPPEntries() {
            const container = document.getElementById('ppEntriesList');
            container.innerHTML = '';
            
            ppEntries.forEach((entry, index) => {
                const isServiceCharge = entry.description.toLowerCase().includes('service fee') || 
                                       entry.description.toLowerCase().includes('service charge');
                const entryId = `pp-entry-${entry.id}`;
                
                const entryDiv = document.createElement('div');
                entryDiv.className = `border rounded-lg p-4 ${isServiceCharge ? 'bg-yellow-50 border-yellow-200' : 'bg-white border-gray-200'}`;
                entryDiv.innerHTML = `
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="${entryId}" 
                               value="${entry.id}" 
                               onchange="togglePPEntry(${entry.id}, ${entry.amount})"
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    ${isServiceCharge ? '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mb-1">Service Charge</span>' : ''}
                                    <p class="text-sm font-medium text-gray-900">${entry.description}</p>
                                    <p class="text-xs text-gray-500 mt-1">Date: ${entry.date} | Invoice: ${entry.invoice_number || 'N/A'}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">UGX ${parseFloat(entry.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(entryDiv);
            });
            
            updateTotalSelected();
        }

        function togglePPEntry(entryId, amount) {
            const checkbox = document.getElementById(`pp-entry-${entryId}`);
            if (checkbox.checked) {
                selectedEntries.add(entryId);
            } else {
                selectedEntries.delete(entryId);
            }
            updateTotalSelected();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAllPP').checked;
            ppEntries.forEach(entry => {
                const checkbox = document.getElementById(`pp-entry-${entry.id}`);
                checkbox.checked = selectAll;
                if (selectAll) {
                    selectedEntries.add(entry.id);
                } else {
                    selectedEntries.clear();
                }
            });
            updateTotalSelected();
        }

        function updateTotalSelected() {
            let total = 0;
            selectedEntries.forEach(entryId => {
                const entry = ppEntries.find(e => e.id === entryId);
                if (entry) {
                    total += parseFloat(entry.amount);
                }
            });
            
            document.getElementById('totalSelectedAmount').textContent = 
                `UGX ${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            const processBtn = document.getElementById('processPayBackBtn');
            if (selectedEntries.size > 0 && total > 0) {
                processBtn.disabled = false;
            } else {
                processBtn.disabled = true;
            }
        }

        function processPayBack() {
            if (selectedEntries.size === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least one item to pay.'
                });
                return;
            }

            const paymentMethod = document.getElementById('paymentMethod').value;
            const selectedEntryIds = Array.from(selectedEntries);
            const totalAmount = selectedEntryIds.reduce((sum, entryId) => {
                const entry = ppEntries.find(e => e.id === entryId);
                return sum + (entry ? parseFloat(entry.amount) : 0);
            }, 0);

            Swal.fire({
                title: 'Confirm Payment',
                html: `Pay <strong>UGX ${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> for ${selectedEntryIds.length} item(s)?<br><br>Payment Method: <strong>${paymentMethod}</strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Process Payment',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processPayment(selectedEntryIds, paymentMethod, totalAmount);
                }
            });
        }

        function processPayment(entryIds, paymentMethod, totalAmount) {
            const processBtn = document.getElementById('processPayBackBtn');
            processBtn.disabled = true;
            processBtn.textContent = 'Processing...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(`/balance-statement/{{ $client->id }}/pay-back`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    entry_ids: entryIds,
                    payment_method: paymentMethod,
                    total_amount: totalAmount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Processed',
                        text: data.message || 'Payment has been processed successfully.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        closePayBackModal();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: data.message || 'Failed to process payment. Please try again.'
                    });
                    processBtn.disabled = false;
                    processBtn.textContent = 'Process Payment';
                }
            })
            .catch(error => {
                console.error('Error processing payment:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing the payment. Please try again.'
                });
                processBtn.disabled = false;
                processBtn.textContent = 'Process Payment';
            });
        }
    </script>

</x-app-layout>

