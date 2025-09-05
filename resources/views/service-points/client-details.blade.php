<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .section-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .section-header {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .section-content {
            padding: 1.5rem;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-partially-done {
            background-color: #fed7aa;
            color: #ea580c;
        }
        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>

    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('service-points.show', $servicePoint) }}" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-3xl font-bold text-[#011478]">Client Details</h2>
                        <p class="text-gray-600 mt-2">{{ $servicePoint->name }} - {{ $client->name }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <div class="text-center">
                        <div class="text-sm text-gray-600">Total Items</div>
                        <div class="text-2xl font-bold text-[#011478]">
                            {{ $pendingItems->count() + $partiallyDoneItems->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Information Section -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="text-lg font-semibold text-gray-900">Client Information</h3>
                </div>
                <div class="section-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                                <p class="text-gray-900 font-medium">{{ $client->name }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                                <p class="text-gray-900">{{ $client->id }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <p class="text-gray-900">{{ $client->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <p class="text-gray-900">{{ $client->email ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <p class="text-gray-900">{{ $client->address ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date</label>
                                <p class="text-gray-900">{{ $client->created_at ? $client->created_at->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Statement Section -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="text-lg font-semibold text-gray-900">Client Statement</h3>
                </div>
                <div class="section-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-sm text-gray-500">Current Balance</p>
                                <button onclick="refreshClientBalance()" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                            <div class="space-y-1">
                                <p class="text-lg font-bold text-gray-900" id="client-balance-display">
                                    <span class="text-blue-600">Available:</span> UGX {{ number_format($client->available_balance ?? 0, 2) }}
                                </p>
                                <p class="text-sm text-gray-500" id="client-total-balance">
                                    <span class="text-gray-600">Total:</span> UGX {{ number_format($client->total_balance ?? 0, 2) }}
                                </p>
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Transactions</p>
                            <p class="text-xl font-bold text-yellow-600">{{ $clientStatement->count() }}</p>
                        </div>
                    </div>
                    
                    @if($clientStatement->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($clientStatement as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-600">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $transaction->description }}</td>
                                            <td class="px-4 py-3 text-gray-600">
                                                <span class="@if($transaction->change_amount > 0) text-green-600 @else text-red-600 @endif">
                                                    {{ $transaction->change_amount > 0 ? '+' : '' }}{{ number_format($transaction->change_amount, 2) }} UGX
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ number_format($transaction->new_balance, 2) }} UGX</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No transaction history available.</p>
                    @endif
                    
                    <div class="mt-4 flex justify-center">
                        <a href="{{ route('balance-statement.show', $client->id) }}" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            View Detailed Balance Statement
                        </a>
                    </div>
                </div>
            </div>

            <!-- Client Notes Section -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="text-lg font-semibold text-gray-900">Client Notes</h3>
                </div>
                <div class="section-content">
                    @if(count($clientNotes) > 0)
                        <div class="space-y-4">
                            @foreach($clientNotes as $note)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $note->title ?? 'Note' }}</span>
                                        <span class="text-xs text-gray-500">{{ $note->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    <p class="text-gray-700">{{ $note->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No notes available for this client.</p>
                    @endif
                </div>
            </div>

            <!-- Ordered Items Section -->
            <div class="section-card">
                <div class="section-header">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Ordered Items (Requests/Orders)</h3>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Total Amount</div>
                            <div class="text-lg font-bold text-blue-600">
                                {{ number_format($pendingItems->sum(function($item) { return $item->price * $item->quantity; }) + $partiallyDoneItems->sum(function($item) { return $item->price * $item->quantity; }), 0) }} UGX
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-content">
                    <form id="itemStatusForm">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Item Name</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Invoice</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Total Amount</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Current Status</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status Update</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @if($pendingItems->count() > 0)
                                        @foreach($pendingItems as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-gray-900 font-medium">
                                                    {{ $item->item->name ?? $item->item_name }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-gray-600 font-semibold">{{ number_format($item->price, 0) }} UGX</td>
                                                <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                                <td class="px-4 py-3 text-gray-600 font-semibold text-green-600">
                                                    {{ number_format($item->price * $item->quantity, 0) }} UGX
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="status-badge status-pending">Pending</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col space-y-2">
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="pending" class="mr-2">
                                                            <span class="text-sm">Not Done</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="partially_done" class="mr-2">
                                                            <span class="text-sm">Partially Done</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="completed" class="mr-2">
                                                            <span class="text-sm">Completed (Done)</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    
                                    @if($partiallyDoneItems->count() > 0)
                                        @foreach($partiallyDoneItems as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-gray-900 font-medium">
                                                    {{ $item->item->name ?? $item->item_name }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-gray-600 font-semibold">{{ number_format($item->price, 0) }} UGX</td>
                                                <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                                <td class="px-4 py-3 text-gray-600 font-semibold text-green-600">
                                                    {{ number_format($item->price * $item->quantity, 0) }} UGX
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="status-badge status-partially-done">In Progress</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col space-y-2">
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="pending" class="mr-2">
                                                            <span class="text-sm">Not Done</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="partially_done" class="mr-2">
                                                            <span class="text-sm">Partially Done</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="completed" class="mr-2">
                                                            <span class="text-sm">Completed (Done)</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    

                                    
                                    @if($pendingItems->count() == 0 && $partiallyDoneItems->count() == 0)
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                                No items found for this client at this service point.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Save and Exit Button -->
            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('service-points.show', $servicePoint) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                    Cancel
                </a>
                <button onclick="saveAndExit()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                    Save and Exit
                </button>
            </div>
        </div>
    </div>

    <script>
        async function refreshClientBalance() {
            try {
                const response = await fetch('/invoices/balance-adjustment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        client_id: {{ $client->id }},
                        total_amount: 0 // Just to get current balance
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const balanceDisplay = document.getElementById('client-balance-display');
                    const totalBalanceDisplay = document.getElementById('client-total-balance');

                    const availableBalance = parseFloat(data.available_balance || data.client_balance || 0);
                    const totalBalance = parseFloat(data.total_balance || data.client_balance || 0);

                    // Update available balance display
                    balanceDisplay.innerHTML = `<span class="text-blue-600">Available:</span> UGX ${availableBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                    // Update total balance display
                    totalBalanceDisplay.innerHTML = `<span class="text-gray-600">Total:</span> UGX ${totalBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Balance Updated',
                        text: `Current balance: UGX ${availableBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to refresh balance'
                    });
                }
            } catch (error) {
                console.error('Error refreshing client balance:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to refresh balance'
                });
            }
        }

        function saveAndExit() {
            // Show confirmation dialog
            Swal.fire({
                title: 'Save Changes?',
                text: 'Are you sure you want to save the selected statuses?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, save changes!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Get form data
                    const form = document.getElementById('itemStatusForm');
                    const formData = new FormData(form);
                    
                    // Debug: Log what's being sent
                    console.log('Form data being sent:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    return fetch('{{ route("service-points.update-statuses-and-process-money", [$servicePoint, $client->id]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: formData
                    })
                    .then(response => {
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            // If not JSON, get the text and log it for debugging
                            return response.text().then(text => {
                                console.error('Non-JSON response received:', text);
                                throw new Error('Server returned non-JSON response. Check console for details.');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'An error occurred');
                        }
                        return data;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saved Successfully!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Redirect back to service point
                        window.location.href = '{{ route("service-points.show", $servicePoint) }}';
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    </script>
</x-app-layout>
