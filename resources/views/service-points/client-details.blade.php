<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Details - ') }}{{ $client->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Service Point: {{ $servicePoint->name }}</span>
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                    Active
                </span>
                <a href="{{ route('service-points.show', $servicePoint) }}" class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    Back to Service Point
                </a>
            </div>
        </div>
    </x-slot>

    <style>
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

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Section 1: Client Summary Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ expanded: true }">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Client Summary Details</h3>
                        <button @click="expanded = !expanded" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <svg x-show="expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                            <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    <div x-show="expanded" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Names</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Client ID</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->id }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Phone</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->email ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Address</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->address ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Registration Date</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->created_at ? $client->created_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Service Point</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $servicePoint->name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Total Items</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $pendingItems->count() + $partiallyDoneItems->count() }}</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Client Statement -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Client Statement</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                    @if(($client->suspense_balance ?? 0) > 0)
                                        <span class="text-orange-600">({{ number_format($client->suspense_balance ?? 0, 2) }} in suspense)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Transactions</p>
                            <p class="text-xl font-bold text-yellow-600">{{ $clientStatement->count() }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('balance-statement.show', $client->id) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            View Balance Statement
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section 3: Client Notes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Client Notes</h3>
                    <div class="border border-gray-200 rounded-lg">
                        <div class="p-4">
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
                            <textarea placeholder="Add notes about this client..." class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent mt-4" rows="3"></textarea>
                            <div class="mt-3 flex justify-end">
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    Save Notes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Ordered Items (Requests/Orders) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Ordered Items (Requests/Orders)</h3>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Total Amount</div>
                            <div class="text-lg font-bold text-blue-600">
                                {{ number_format($correctTotalAmount ?? 0, 0) }} UGX
                            </div>
                        </div>
                    </div>
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
