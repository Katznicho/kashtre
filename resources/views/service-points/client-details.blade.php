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
                            <p class="text-sm text-gray-500 mb-1">Age</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->age ?? 'N/A' }} years</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Sex</p>
                            <p class="text-lg font-semibold text-gray-900">{{ ucfirst($client->sex) }}</p>
                    </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Client ID</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->client_id }}</p>
                </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Visit ID</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->visit_id }}</p>
            </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Payment Methods</p>
                            <div class="flex flex-wrap gap-1 mb-2 payment-methods-display">
                                @if($client->payment_methods && count($client->payment_methods) > 0)
                                    @foreach($client->payment_methods as $index => $method)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $index + 1 }}. {{ ucwords(str_replace('_', ' ', $method)) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-sm text-gray-500">No payment methods specified</span>
                                @endif
                </div>
                            <button onclick="openPaymentMethodsModal()" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                Edit Payment Methods
                            </button>
                            </div>
                        <div id="payment-phone-section" class="bg-gray-50 p-4 rounded-lg border-2 border-dashed border-blue-200 hover:border-blue-300 transition-colors" style="display: none;">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-500 font-medium">Payment Phone Number</p>
                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Editable</span>
                            </div>
                            <div class="space-y-2">
                                <input type="tel"
                                       id="payment-phone-edit"
                                       value="{{ $client->payment_phone_number ?? '' }}"
                                       placeholder="Enter payment phone number"
                                       class="w-full text-lg font-semibold text-gray-900 bg-white border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <button type="button" onclick="savePaymentPhone()"
                                        class="w-full bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Save Payment Phone
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Contact Phone Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->phone_number }}</p>
                            </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Email Address</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $client->email ?? 'N/A' }}</p>
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

            {{-- Make a Request/Order Section - Same as POS page --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Make a Request/Order</h3>
                    
                    <!-- Main POS Interface - Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Left Column: Item Selection -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Select Item</h4>
                            
                            <!-- Search Bar -->
                            <div class="mb-4">
                                <div class="relative">
                                    <input type="text" id="search-input" placeholder="Search items..." 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Simple Options -->
                            <div class="flex items-center space-x-4 mb-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="show-prices" class="rounded border-gray-300">
                                    <span class="text-sm text-gray-700">Show Prices</span>
                                </label>
                                
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="show-descriptions" class="rounded border-gray-300">
                                    <span class="text-sm text-gray-700">Show Descriptions</span>
                                </label>
                            </div>
                            
                            <!-- Items Table -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-700">Item</span>
                                            <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Quantity To Sell</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="items-container" class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                    @forelse($items as $item)
                                    <div class="item-row px-4 py-3 hover:bg-gray-50" data-item-name="{{ strtolower($item->name) }}" data-item-display-name="{{ $item->display_name }}" data-item-other-names="{{ strtolower($item->other_names ?? '') }}" data-item-type="{{ $item->type ?? 'N/A' }}">
                                        <div class="grid grid-cols-2 gap-4 items-center">
                                            <div>
                                                <span class="text-sm text-gray-900">{{ $item->display_name }}</span>
                                                @php
                                                    // Generate dynamic description based on item properties
                                                    $description = '';
                                                    if ($item->description && !empty(trim($item->description))) {
                                                        $description = $item->description;
                                                    } else {
                                                        // Generate dynamic description based on item type and properties
                                                        $descriptionParts = [];
                                                        
                                                        // Add type-specific description based on item name and type
                                                        $itemName = strtolower($item->display_name ?? $item->name);
                                                        
                                                        // Generate intelligent descriptions based on item name patterns
                                                        if (str_contains($itemName, 'amoxicillin')) {
                                                            $descriptionParts[] = 'Antibiotic medication for bacterial infections';
                                                        } elseif (str_contains($itemName, 'paracetamol') || str_contains($itemName, 'acetaminophen')) {
                                                            $descriptionParts[] = 'Pain relief and fever reducer';
                                                        } elseif (str_contains($itemName, 'ibuprofen')) {
                                                            $descriptionParts[] = 'Anti-inflammatory pain relief';
                                                        } elseif (str_contains($itemName, 'vitamin') || str_contains($itemName, 'supplement')) {
                                                            $descriptionParts[] = 'Nutritional supplement';
                                                        } elseif (str_contains($itemName, 'hair') || str_contains($itemName, 'shampoo') || str_contains($itemName, 'conditioner')) {
                                                            $descriptionParts[] = 'Hair care product';
                                                        } elseif (str_contains($itemName, 'treatment') || str_contains($itemName, 'therapy')) {
                                                            $descriptionParts[] = 'Therapeutic treatment service';
                                                        } else {
                                                            // Fallback to type-based description
                                                            switch ($item->type) {
                                                                case 'bulk':
                                                                    $descriptionParts[] = 'Bulk package containing multiple items';
                                                                    break;
                                                                case 'package':
                                                                    $descriptionParts[] = 'Service package with included items';
                                                                    break;
                                                                case 'service':
                                                                    $descriptionParts[] = 'Professional service';
                                                                    break;
                                                                case 'good':
                                                                default:
                                                                    $descriptionParts[] = 'Product item';
                                                                    break;
                                                            }
                                                        }
                                                        
                                                        // Add item variation info if present in name
                                                        if (str_contains($itemName, 'advanced') || str_contains($itemName, 'premium') || str_contains($itemName, 'deluxe') || str_contains($itemName, 'professional') || str_contains($itemName, 'enhanced')) {
                                                            $descriptionParts[] = 'Premium quality variant';
                                                        } elseif (str_contains($itemName, 'basic') || str_contains($itemName, 'standard')) {
                                                            $descriptionParts[] = 'Standard quality variant';
                                                        }
                                                        
                                                        // Add category if available
                                                        if ($item->category && !empty(trim($item->category))) {
                                                            $descriptionParts[] = "Category: {$item->category}";
                                                        }
                                                        
                                                        // Add other names if available
                                                        if ($item->other_names && !empty(trim($item->other_names))) {
                                                            $descriptionParts[] = "Also known as: {$item->other_names}";
                                                        }
                                                        
                                                        // Add unit information if available
                                                        if ($item->unit && !empty(trim($item->unit))) {
                                                            $descriptionParts[] = "Unit: {$item->unit}";
                                                        }
                                                        
                                                        $description = implode(' • ', $descriptionParts);
                                                    }
                                                @endphp
                                                @if($description)
                                                <p class="text-xs text-gray-500 mt-1 description-display">{{ $description }}</p>
                                                @endif
                                                <p class="text-xs text-blue-600 mt-1 price-display" style="display: none;">
                                                    Price: UGX {{ number_format($item->final_price ?? 0, 2) }}
                                                    @if(isset($item->final_price) && $item->final_price != $item->default_price)
                                                        <span class="text-green-600">(Branch Price)</span>
                                                    @else
                                                        <span class="text-gray-500">(Default Price)</span>
                                                    @endif
                                                    @if($item->vat_rate && $item->vat_rate > 0)
                                                        <span class="text-orange-600">(VAT: {{ $item->vat_rate }}%)</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <input type="number" min="0" value="0" 
                                                       class="quantity-input w-20 px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                       data-item-id="{{ $item->id }}" 
                                                       data-item-price="{{ $item->final_price ?? 0 }}"
                                                       data-item-name="{{ $item->name }}"
                                                       data-item-display-name="{{ $item->display_name }}"
                                                       data-item-other-names="{{ $item->other_names ?? '' }}"
                                                       data-item-type="{{ $item->type ?? 'N/A' }}">
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="px-4 py-8">
                                        <p class="text-sm text-gray-500 text-center">No items available for this hospital</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                <span>Showing 1 to {{ min(5, count($items)) }} of {{ count($items) }} results</span>
                                <div class="flex items-center space-x-2">
                                    <select class="px-2 py-1 border border-gray-300 rounded-md">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                    </select>
                                    <span>Per page</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column: Request/Order Summary -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Request/Order Summary</h4>
                            
                            <!-- Request/Order Summary Table -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <div class="grid grid-cols-5 gap-4">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Item</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Type</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Quantity</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Price</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Action</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="request-order-summary-items" class="divide-y divide-gray-200 min-h-32">
                                    <div class="px-4 py-8">
                                        <p class="text-sm text-gray-500 text-center">No items selected</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Request/Order Summary -->
                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Unique Items:</span>
                                    <span id="total-items" class="text-sm font-medium text-gray-900">0</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Total Quantity:</span>
                                    <span id="total-quantity" class="text-sm font-medium text-gray-900">0</span>
                                </div>
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm text-gray-600">Total Amount:</span>
                                    <span id="total-amount" class="text-lg font-bold text-gray-900">UGX 0.00</span>
                                </div>
                                <button class="w-full bg-gray-900 text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors" onclick="showInvoicePreview()">
                                    Preview Proforma Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unified Ordered Items Component --}}
            <x-pos.ordered-items 
                :pendingItems="$pendingItems" 
                :partiallyDoneItems="$partiallyDoneItems" 
                :completedItems="collect()" 
                :correctTotalAmount="$correctTotalAmount ?? 0" 
            />

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

        function openPaymentMethodsModal() {
            document.getElementById('payment-methods-modal').classList.remove('hidden');
        }
        
        function closePaymentMethodsModal() {
            document.getElementById('payment-methods-modal').classList.add('hidden');
        }
        
        function savePaymentMethods() {
            const selectedMethods = [];
            const checkboxes = document.querySelectorAll('input[name="payment_methods[]"]:checked');
            
            checkboxes.forEach(checkbox => {
                selectedMethods.push(checkbox.value);
            });
            
            // Send AJAX request to update payment methods
            fetch(`/clients/{{ $client->id }}/update-payment-methods`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_methods: selectedMethods
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the display
                    const displayContainer = document.querySelector('.payment-methods-display');
                    if (displayContainer) {
                        if (selectedMethods.length > 0) {
                            let displayHTML = '';
                            selectedMethods.forEach((method, index) => {
                                displayHTML += `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${index + 1}. ${ucwords(method.replace('_', ' '))}</span>`;
                            });
                            displayContainer.innerHTML = displayHTML;
                        } else {
                            displayContainer.innerHTML = '<span class="text-sm text-gray-500">No payment methods specified</span>';
                        }
                    }
                    
                    // Close modal
                    closePaymentMethodsModal();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Methods Updated',
                        text: 'Payment methods have been saved successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update payment methods'
                    });
                }
            })
            .catch(error => {
                console.error('Error updating payment methods:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update payment methods'
                });
            });
        }
        
        function ucwords(str) {
            return str.replace(/\b\w/g, l => l.toUpperCase());
        }

        function savePaymentPhone() {
            const phoneInput = document.getElementById('payment-phone-edit');
            const phoneNumber = phoneInput.value;
            
            if (!phoneNumber) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter a payment phone number',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }
            
            Swal.fire({
                title: 'Save Payment Phone?',
                text: `Save "${phoneNumber}" as the payment phone number?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saved!',
                        text: 'Payment phone number has been saved.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // POS Functionality - Same as POS page
        let cart = [];

        // Add event listeners to quantity inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Check initial payment methods for mobile money
            const initialPaymentMethods = @json($client->payment_methods ?? []);
            if (initialPaymentMethods.includes('mobile_money')) {
                document.getElementById('payment-phone-section').style.display = 'block';
            }
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const itemName = this.dataset.itemName;
                    const itemDisplayName = this.dataset.itemDisplayName;
                    const rawPrice = this.dataset.itemPrice;
                    const itemPrice = parseFloat(rawPrice) || 0;
                    const quantity = parseInt(this.value) || 0;
                    const itemType = this.dataset.itemType || 'N/A';
                    
                    // Debug logging
                    console.log('Item:', itemName, 'Raw Price:', rawPrice, 'Parsed Price:', itemPrice, 'Quantity:', quantity, 'Type:', itemType);
                    
                    if (quantity > 0) {
                        addToCart(itemId, itemName, itemPrice, quantity, itemType, itemDisplayName);
                        // Don't reset the input value - keep the state
                    } else if (quantity === 0) {
                        // Remove item from cart if quantity is 0
                        removeFromCartByItemId(itemId);
                    }
                });
            });
            
            // Add search functionality
            const searchInput = document.getElementById('search-input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const itemRows = document.querySelectorAll('.item-row');
                
                itemRows.forEach(row => {
                    const itemName = row.dataset.itemName;
                    const itemOtherNames = row.dataset.itemOtherNames;
                    
                    // Search in both item name and other names
                    if (itemName.includes(searchTerm) || itemOtherNames.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Add show prices functionality
            const showPricesCheckbox = document.getElementById('show-prices');
            showPricesCheckbox.addEventListener('change', function() {
                const priceElements = document.querySelectorAll('.price-display');
                priceElements.forEach(element => {
                    element.style.display = this.checked ? 'block' : 'none';
                });
            });
            
            // Add show descriptions functionality
            const showDescriptionsCheckbox = document.getElementById('show-descriptions');
            showDescriptionsCheckbox.addEventListener('change', function() {
                const descriptionElements = document.querySelectorAll('.description-display');
                descriptionElements.forEach(element => {
                    element.style.display = this.checked ? 'block' : 'none';
                });
            });
        });

        function addToCart(itemId, itemName, itemPrice, quantity, itemType, displayName) {
            // Ensure proper number types
            const price = parseFloat(itemPrice) || 0;
            const qty = parseInt(quantity) || 0;
            
            // Check if item already exists in cart
            const existingItem = cart.find(item => item.id === itemId);
            if (existingItem) {
                existingItem.quantity = qty; // Update quantity instead of adding
            } else {
                cart.push({
                    id: itemId,
                    name: itemName,
                    displayName: displayName,
                    price: price,
                    quantity: qty,
                    type: itemType || 'N/A'
                });
            }
            
            updateRequestOrderSummaryDisplay();
        }
        
        function removeFromCartByItemId(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateRequestOrderSummaryDisplay();
        }
        
        function updateRequestOrderSummaryDisplay() {
            const requestOrderSummaryContainer = document.getElementById('request-order-summary-items');
            const totalItemsSpan = document.getElementById('total-items');
            const totalQuantitySpan = document.getElementById('total-quantity');
            const totalAmountSpan = document.getElementById('total-amount');
            
            if (cart.length === 0) {
                requestOrderSummaryContainer.innerHTML = '<div class="px-4 py-8"><p class="text-sm text-gray-500 text-center">No items selected</p></div>';
                totalItemsSpan.textContent = '0';
                totalQuantitySpan.textContent = '0';
                totalAmountSpan.textContent = 'UGX 0.00';
                return;
            }
            
            let requestOrderSummaryHTML = '';
            let totalItems = 0;
            let totalQuantity = 0;
            let totalAmount = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = parseFloat(item.price || 0) * parseInt(item.quantity || 0);
                totalItems += 1; // Count unique items
                totalQuantity += parseInt(item.quantity || 0); // Sum of all quantities
                totalAmount += itemTotal;
                
                requestOrderSummaryHTML += `
                    <div class="px-4 py-3">
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <div>
                                <span class="text-sm text-gray-900 font-medium">${item.displayName || item.name}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">${item.type || 'N/A'}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-900">${item.quantity}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-blue-600">UGX ${(item.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                <button class="text-red-500 hover:text-red-700 text-sm ml-2" onclick="removeFromCart(${index})">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            requestOrderSummaryContainer.innerHTML = requestOrderSummaryHTML;
            totalItemsSpan.textContent = totalItems; // This now shows total quantity of all items
            totalQuantitySpan.textContent = totalQuantity; // This now shows total quantity of all items
            totalAmountSpan.textContent = `UGX ${parseFloat(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateRequestOrderSummaryDisplay();
        }

        async function showInvoicePreview() {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Cart',
                    text: 'Please add items to cart before previewing invoice'
                });
                return;
            }
            
            // Generate invoice number
            try {
                const response = await fetch('/invoices/generate-invoice-number', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        business_id: {{ auth()->user()->business->id }}
                    })
                });
                
                const data = await response.json();
                if (data.invoice_number) {
                    document.getElementById('invoice-number-display').textContent = data.invoice_number;
                } else {
                    document.getElementById('invoice-number-display').textContent = 'Error generating invoice number';
                }
            } catch (error) {
                console.error('Error generating invoice number:', error);
                document.getElementById('invoice-number-display').textContent = 'Error generating invoice number';
            }
            
            // Populate invoice items table
            const invoiceTable = document.getElementById('invoice-items-table');
            let tableHTML = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = (item.price || 0) * (item.quantity || 0);
                subtotal += itemTotal;
                
                tableHTML += `
                    <tr class="bg-white">
                        <td class="border border-gray-300 px-4 py-2">${item.displayName || item.name}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">${item.type || 'N/A'}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">${item.quantity}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${(item.price || 0).toLocaleString()}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${itemTotal.toLocaleString()}</td>
                    </tr>
                `;
            });
            
            invoiceTable.innerHTML = tableHTML;
            
            // Calculate totals
            document.getElementById('invoice-subtotal').textContent = `UGX ${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('invoice-total').textContent = `UGX ${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            // Show the modal
            document.getElementById('invoice-preview-modal').classList.remove('hidden');
        }
        
        function closeInvoicePreview() {
            document.getElementById('invoice-preview-modal').classList.add('hidden');
        }
        
        function closeClientConfirmation() {
            document.getElementById('client-confirmation-modal').classList.add('hidden');
        }
        
        function printClientDetails() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Client Details - {{ $client->name }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .client-info { margin-bottom: 20px; }
                            .qr-code { text-align: center; margin: 20px 0; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>{{ auth()->user()->business->name ?? 'Medical Centre' }}</h1>
                        </div>
                        <div class="client-info">
                            <h2>Client Details</h2>
                            <p><strong>Name:</strong> {{ $client->name }}</p>
                            <p><strong>Client ID:</strong> {{ $client->client_id }}</p>
                            <p><strong>Visit ID:</strong> {{ $client->visit_id }}</p>
                            <p><strong>Branch:</strong> {{ auth()->user()->currentBranch->name ?? 'N/A' }}</p>
                        </div>
                        <div class="qr-code">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=128x128&data={{ urlencode($client->client_id . '|' . $client->name) }}" alt="QR Code">
                        </div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        function saveAndExit() {
            // Log the save and exit action
            console.log('=== SERVICE POINTS CLIENT DETAILS - SAVE AND EXIT TRIGGERED ===', {
                client_id: {{ $client->id }},
                client_name: '{{ $client->name }}',
                page: 'Service Points Client Details',
                action: 'Save and Exit',
                timestamp: new Date().toISOString()
            });

            // Show confirmation dialog
            Swal.fire({
                title: 'Save Changes?',
                text: 'Are you sure you want to save the selected statuses?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, save changes!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('=== SERVICE POINTS CLIENT DETAILS - SAVE CONFIRMED ===', {
                        client_id: {{ $client->id }},
                        action: 'Save Confirmed',
                        redirect_to: 'service-points.show',
                        timestamp: new Date().toISOString()
                    });

                    Swal.fire({
                        title: 'Saved Successfully!',
                        text: 'Changes have been saved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Redirect back to service point
                        window.location.href = '{{ route("service-points.show", $servicePoint) }}';
                    });
                } else {
                    console.log('=== SERVICE POINTS CLIENT DETAILS - SAVE CANCELLED ===', {
                        client_id: {{ $client->id }},
                        action: 'Save Cancelled',
                        timestamp: new Date().toISOString()
                    });
                }
            });
        }
    </script>

    <!-- Payment Methods Modal -->
    <div id="payment-methods-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Select Payment Methods</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Choose how the client will pay for this order.</p>
                    </div>
                </div>
                
                <!-- Payment Method Options -->
                <div class="mt-5 sm:mt-6 space-y-3">
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="payment_methods[]" value="cash" class="mr-3 rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Cash Payment</span>
                    </label>
                    
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="payment_methods[]" value="mobile_money" class="mr-3 rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Mobile Money</span>
                    </label>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" onclick="confirmAndSaveInvoice()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Confirm Order
                    </button>
                    <button type="button" onclick="closePaymentMethodsModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order/Request Summary Modal -->
    <div id="invoice-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Order Summary</h3>
                <button onclick="closeInvoicePreview()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Proforma Invoice Header -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Proforma Invoice</h2>
                <div class="bg-blue-600 text-white py-2 px-4 rounded-lg mb-2">
                    <span class="text-lg font-semibold">Proforma Invoice</span>
                </div>
                <div class="bg-gray-100 py-2 px-4 rounded-lg border">
                    <span class="text-sm text-gray-600">Proforma Invoice Number:</span>
                    <span id="invoice-number-display" class="text-lg font-bold text-gray-800 ml-2">Generating...</span>
                </div>
            </div>
            
            <!-- Invoice Items Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-table" class="bg-white divide-y divide-gray-200">
                        <!-- Invoice items will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Invoice Totals -->
            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Subtotal:</span>
                    <span id="invoice-subtotal" class="text-sm font-medium text-gray-900">UGX 0.00</span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                    <span id="invoice-total" class="text-lg font-bold text-gray-900">UGX 0.00</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeInvoicePreview()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Close
                </button>
                <button onclick="openPaymentMethodsModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Proceed to Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Methods Modal -->
    <div id="payment-methods-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Payment Methods</h3>
                    <button onclick="closePaymentMethodsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="insurance" 
                                   {{ in_array('insurance', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏥 Insurance</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="credit_arrangement_institutions" 
                                   {{ in_array('credit_arrangement_institutions', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏦 Credit Arrangement Institutions</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="mobile_money" 
                                   {{ in_array('mobile_money', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">📱 Mobile Money</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="v_card" 
                                   {{ in_array('v_card', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">💳 V Card (Virtual Card)</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="p_card" 
                                   {{ in_array('p_card', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">💳 P Card (Physical Card)</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="bank_transfer" 
                                   {{ in_array('bank_transfer', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏦 Bank Transfer</span>
                        </label>
                        
                        <label class="flex items-center">
                                <span class="text-gray-600"> (Qty: ${package.quantity})</span>
                            </div>
                            <div class="text-right">
                                <span class="text-blue-600 font-mono font-semibold">${trackingNumber}</span>
                            </div>
                        </div>
                    `;
                });
                
                trackingList.innerHTML = trackingHTML;
                trackingSummaryContainer.classList.remove('hidden');
            } else {
                document.getElementById('package-tracking-summary').classList.add('hidden');
            }
            
            // Calculate balance adjustment first (needed for service charge calculation)
            const balanceAdjustmentData = await calculateBalanceAdjustment(subtotal);
            const balanceAdjustment = parseFloat(balanceAdjustmentData.balance_adjustment) || 0;
            
            // Calculate totals according to correct formula:
            // Subtotal 1 = Sum of all items (already calculated as 'subtotal')
            // Subtotal 2 = Subtotal 1 - Package Adjustment - Account Balance Adjustment
            // Total = Subtotal 2 + Service Charge
            // Service Charge is calculated based on Subtotal 2
            
            const subtotal1 = parseFloat(subtotal);
            let subtotal2 = subtotal1 - packageAdjustment - balanceAdjustment;
            
            // Ensure subtotal2 never goes below 0
            if (subtotal2 < 0) {
                subtotal2 = 0;
            }
            
            console.log('Service charge calculation - subtotal2:', subtotal2);
            const serviceChargeData = await calculateServiceCharge(subtotal2);
            const serviceCharge = serviceChargeData.amount;
            console.log('Service charge result:', serviceCharge);
            
            const finalTotal = subtotal2 + parseFloat(serviceCharge);
            
            // Update financial summary
            document.getElementById('invoice-subtotal').textContent = `UGX ${subtotal1.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('package-adjustment-display').textContent = `UGX ${packageAdjustment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('balance-adjustment-display').textContent = `UGX ${balanceAdjustment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('invoice-subtotal-2').textContent = `UGX ${subtotal2.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Display service charge and handle note visibility
            const serviceChargeElement = document.getElementById('service-charge-display');
            const serviceChargeNote = document.getElementById('service-charge-note');
            
            // Always show the service charge amount
            serviceChargeElement.textContent = `UGX ${parseFloat(serviceCharge).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Show/hide note based on whether service charge ranges exist
            if (serviceChargeData.hasRanges) {
                // Service charge ranges are configured, hide the note
                serviceChargeNote.style.display = 'none';
            } else {
                // No service charge ranges configured, show the note
                serviceChargeNote.style.display = 'block';
            }
            
            document.getElementById('invoice-final-total').textContent = `UGX ${finalTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Show modal
            document.getElementById('invoice-modal').classList.remove('hidden');
        }

        function closeInvoicePreview() {
            document.getElementById('invoice-modal').classList.add('hidden');
            
            // Reset service charge display to default state
            const serviceChargeElement = document.getElementById('service-charge-display');
            const serviceChargeNote = document.getElementById('service-charge-note');
            if (serviceChargeElement && serviceChargeNote) {
                serviceChargeElement.textContent = 'UGX 0.00';
                serviceChargeNote.style.display = 'none'; // Keep the note hidden
            }
            
            // Reset package tracking numbers
            if (window.packageTrackingNumbers) {
                window.packageTrackingNumbers.clear();
            }
        }

        async function calculatePackageAdjustment() {
            try {
                const response = await fetch('/invoices/package-adjustment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: selectedItems,
                        client_id: {{ $client->id }},
                        business_id: {{ auth()->user()->business_id }},
                        branch_id: {{ auth()->user()->currentBranch->id ?? 'null' }}
                    })
                });
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error calculating package adjustment:', error);
                return { total_adjustment: 0, details: [] };
            }
        }

        async function calculateServiceCharge(subtotal) {
            console.log('calculateServiceCharge called with subtotal:', subtotal);
            try {
                const response = await fetch('/invoices/service-charge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        subtotal: subtotal,
                        business_id: {{ auth()->user()->business_id }},
                        branch_id: {{ auth()->user()->currentBranch->id ?? 'null' }}
                    })
                });
                
                const data = await response.json();
                console.log('Service charge API response:', data);
                if (data.success) {
                    const serviceCharge = parseFloat(data.service_charge) || 0;
                    const hasServiceChargeRanges = data.has_service_charge_ranges || false;
                    return {
                        amount: serviceCharge,
                        hasRanges: hasServiceChargeRanges
                    };
                } else {
                    console.error('Service charge calculation failed:', data.message);
                    return { amount: 0, hasRanges: false };
                }
            } catch (error) {
                console.error('Error calculating service charge:', error);
                return { amount: 0, hasRanges: false };
            }
        }

        async function calculateBalanceAdjustment(amount) {
            try {
                const response = await fetch('/invoices/balance-adjustment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        total_amount: amount,
                        client_id: {{ $client->id }}
                    })
                });
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error calculating balance adjustment:', error);
                return { balance_adjustment: 0 };
            }
        }

        async function confirmAndSaveInvoice() {
            if (selectedItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Cart',
                    text: 'Please add items to cart before saving invoice'
                });
                return;
            }
            
            // Check if service charge is required and present
            const serviceChargeElement = document.getElementById('service-charge-display');
            const serviceChargeNote = document.getElementById('service-charge-note');
            const serviceChargeText = serviceChargeElement ? serviceChargeElement.textContent : 'UGX 0.00';
            const serviceChargeValue = parseFloat(serviceChargeText.replace(/[^0-9.-]/g, '')) || 0;
            
            // Check if service charges are not configured (note is visible)
            const isServiceChargeNotConfigured = serviceChargeNote && serviceChargeNote.style.display !== 'none';
            
            // Debug logging for service charge validation
            console.log('=== SERVICE CHARGE VALIDATION DEBUG ===');
            console.log('Service charge validation debug:', {
                serviceChargeValue: serviceChargeValue,
                serviceChargeText: serviceChargeText,
                isServiceChargeNotConfigured: isServiceChargeNotConfigured,
                noteDisplay: serviceChargeNote ? serviceChargeNote.style.display : 'note not found',
                serviceChargeElement: serviceChargeElement ? 'found' : 'not found',
                serviceChargeNote: serviceChargeNote ? 'found' : 'not found',
                willBlock: isServiceChargeNotConfigured,
                timestamp: new Date().toISOString()
            });
            
            // Check if this is a package invoice (has package adjustment)
            const packageAdjustmentElement = document.getElementById('package-adjustment-display');
            const packageAdjustmentText = packageAdjustmentElement ? packageAdjustmentElement.textContent : 'UGX 0.00';
            const packageAdjustmentValue = parseFloat(packageAdjustmentText.replace(/[^0-9.-]/g, '')) || 0;
            const isPackageInvoice = packageAdjustmentValue > 0;
            
            // Only block if service charges are not configured at all
            // OR if it's a non-package invoice with no service charge
            if (isServiceChargeNotConfigured || (!isPackageInvoice && serviceChargeValue <= 0)) {
                const errorTitle = isServiceChargeNotConfigured ? 'Service Charges Not Configured' : 'Service Charge Required';
                const errorMessage = isServiceChargeNotConfigured 
                    ? 'Service charges not configured. Please contact support.'
                    : 'Service charge not configured. Please contact support.';
                
                Swal.fire({
                    icon: 'error',
                    title: errorTitle,
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Confirm with SweetAlert2
            const result = await Swal.fire({
                title: 'Confirm Proforma Invoice',
                text: 'Are you sure you want to save this proforma invoice?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            });
            
            if (!result.isConfirmed) {
                return;
            }
            
            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Saving...';
            button.disabled = true;
            
            try {
                // Calculate totals
                let subtotal = 0;
                selectedItems.forEach(item => {
                    subtotal += parseFloat(item.price || 0) * parseInt(item.quantity || 0);
                });
                
                const packageAdjustmentData = await calculatePackageAdjustment();
                const packageAdjustment = parseFloat(packageAdjustmentData.total_adjustment) || 0;
                
                // Calculate balance adjustment first (needed for service charge calculation)
                const balanceAdjustmentData = await calculateBalanceAdjustment(subtotal);
                const balanceAdjustment = parseFloat(balanceAdjustmentData.balance_adjustment) || 0;
                
                // Calculate totals according to correct formula:
                // Subtotal 1 = Sum of all items (already calculated as 'subtotal')
                // Subtotal 2 = Subtotal 1 - Package Adjustment - Account Balance Adjustment
                // Total = Subtotal 2 + Service Charge
                // Service Charge is calculated based on Subtotal 2
                
                const subtotal1 = parseFloat(subtotal);
                let subtotal2 = subtotal1 - packageAdjustment - balanceAdjustment;
                
                // Ensure subtotal2 never goes below 0
                if (subtotal2 < 0) {
                    subtotal2 = 0;
                }
                
                const serviceChargeData = await calculateServiceCharge(subtotal2);
                const serviceCharge = serviceChargeData.amount;
                let totalAmount = subtotal2 + parseFloat(serviceCharge);
                
                // Ensure totalAmount never goes below 0
                if (totalAmount < 0) {
                    totalAmount = 0;
                }
                
                // Get payment phone and methods
                const paymentPhone = document.getElementById('payment-phone-edit')?.value || '';
                const paymentMethods = Array.from(document.querySelectorAll('input[name="payment_methods[]"]:checked'))
                    .map(cb => cb.value);
                
                // Check if mobile money is selected and process payment
                let paymentResult = null;
                let amountPaid = 0;
                
                // Only process mobile money payment if total amount > 0
                if (paymentMethods.includes('mobile_money') && paymentPhone && totalAmount > 0) {
                    console.log('=== PROCESSING MOBILE MONEY PAYMENT ===');
                    console.log('Processing mobile money payment:', { 
                        totalAmount, 
                        paymentPhone,
                        totalAmountType: typeof totalAmount,
                        isNaN: isNaN(totalAmount),
                        parseFloatResult: parseFloat(totalAmount)
                    });
                    
                    // Show payment processing dialog
                    Swal.fire({
                        title: 'Processing Mobile Money Payment',
                        html: `
                            <div class="text-center">
                                <div class="mb-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                                </div>
                                <p class="text-lg font-semibold">UGX ${parseFloat(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                <p class="text-sm text-gray-600">To: ${paymentPhone}</p>
                                <p class="text-sm text-gray-500 mt-2">Please wait while we process your payment...</p>
                            </div>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                    
                    // Process mobile money payment
                    paymentResult = await processMobileMoneyPayment(totalAmount, paymentPhone);
                    
                    if (paymentResult.success) {
                        amountPaid = totalAmount;
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Successful!',
                            html: `
                                <div class="text-center">
                                    <p class="text-lg font-semibold text-green-600">UGX ${parseFloat(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                    <p class="text-sm text-gray-600">Transaction ID: ${paymentResult.transaction_id}</p>
                                    <p class="text-sm text-gray-500">Paid via Mobile Money</p>
                                </div>
                            `,
                            confirmButtonText: 'Great!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: 'Mobile money payment could not be processed. Please try again.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                } else if (paymentMethods.includes('mobile_money') && totalAmount === 0) {
                    console.log('=== SKIPPING MOBILE MONEY PAYMENT - ZERO AMOUNT ===');
                    console.log('Mobile money payment skipped because total amount is 0:', {
                        totalAmount: totalAmount,
                        paymentMethods: paymentMethods,
                        paymentPhone: paymentPhone
                    });
                }
                
                // Prepare items with totals
                const itemsWithTotals = selectedItems.map(item => ({
                    ...item,
                    total_amount: parseFloat(item.price || 0) * parseInt(item.quantity || 0)
                }));
                
                // Create invoice data
                const invoiceData = {
                    invoice_number: document.getElementById('invoice-number-display').textContent,
                    business_id: {{ auth()->user()->business_id }},
                    client_id: {{ $client->id }},
                    branch_id: {{ auth()->user()->currentBranch->id ?? 'null' }},
                    service_point_id: {{ $servicePoint->id }},
                    created_by: {{ auth()->id() }},
                    client_name: '{{ $client->name }}',
                    client_phone: '{{ $client->phone_number }}',
                    payment_phone: paymentPhone,
                    visit_id: '{{ $client->visit_id }}',
                    items: itemsWithTotals,
                    subtotal: parseFloat(subtotal),
                    package_adjustment: parseFloat(packageAdjustment),
                    service_charge: parseFloat(serviceCharge),
                    balance_adjustment: parseFloat(balanceAdjustment),
                    total_amount: parseFloat(totalAmount),
                    amount_paid: parseFloat(amountPaid),
                    balance_due: parseFloat(totalAmount - amountPaid),
                    payment_methods: paymentMethods,
                    payment_status: amountPaid >= totalAmount ? 'paid' : 'pending',
                    status: 'confirmed',
                    notes: ''
                };
                
                // Send invoice data to backend
                const response = await fetch('/invoices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(invoiceData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const invoiceNumber = result.invoice.invoice_number;
                    
                    // Clear the cart and close modal
                    selectedItems = [];
                    updateOrderSummary();
                    closeInvoicePreview();
                    
                    // Show success with options
                    const result = await Swal.fire({
                        icon: 'success',
                        title: 'Proforma Invoice Saved!',
                        html: `
                            <div class="text-center">
                                <p class="mb-2">Proforma invoice has been saved successfully.</p>
                                <p class="text-sm text-gray-600">Proforma Invoice Number: <strong>${invoiceNumber}</strong></p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'View Proforma Invoice',
                        cancelButtonText: 'Print Proforma Invoice',
                        showDenyButton: true,
                        denyButtonText: 'Stay Here'
                    });
                    
                    if (result.isConfirmed) {
                        // View invoice
                        window.location.href = `/invoices/${data.invoice.id}`;
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Print invoice
                        const printWindow = window.open(`/invoices/${data.invoice.id}/print`, '_blank');
                        
                        // Show print confirmation
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'info',
                                title: 'Print Window Opened',
                                html: `
                                    <div class="text-center">
                                        <p class="mb-2">Proforma invoice print window has been opened.</p>
                                        <p class="text-sm text-gray-600">Proforma Invoice Number: <strong>${invoiceNumber}</strong></p>
                                        <p class="text-xs text-gray-500 mt-2">Please check the new tab/window for printing options.</p>
                                    </div>
                                `,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }, 1000);
                    }
                    // If "Stay Here" is clicked, do nothing
                    
                } else {
                    throw new Error(result.message || 'Failed to save invoice');
                }
                
            } catch (error) {
                console.error('Error saving invoice:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save invoice: ' + error.message,
                    confirmButtonText: 'OK'
                });
            } finally {
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
            }
        }

        async function processMobileMoneyPayment(amount, phoneNumber) {
            try {
                // Prepare payment data
                const paymentData = {
                    amount: amount,
                    phone_number: phoneNumber,
                    client_id: {{ $client->id }},
                    business_id: {{ auth()->user()->business_id }},
                    items: selectedItems,
                    invoice_number: document.getElementById('invoice-number-display').textContent
                };
                
                // Send payment request to backend
                const response = await fetch('/invoices/mobile-money-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(paymentData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    return {
                        success: true,
                        transaction_id: result.transaction_id,
                        amount: amount,
                        phone: phoneNumber,
                        status: result.status || 'success',
                        message: result.message || 'Payment processed successfully',
                        description: result.description
                    };
                } else {
                    return {
                        success: false,
                        message: result.message || 'Payment failed',
                        error: result.error
                    };
                }
            } catch (error) {
                console.error('Mobile money payment error:', error);
                return {
                    success: false,
                    message: 'Error processing mobile money payment',
                    error: error.message
                };
            }
        }
    </script>

    <!-- Order/Request Summary Modal -->
    <div id="invoice-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Proforma Invoice Header -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Proforma Invoice</h2>
                    <div class="bg-blue-600 text-white py-2 px-4 rounded-lg mb-2">
                        <span class="text-lg font-semibold">Proforma Invoice</span>
                    </div>
                    <div class="bg-gray-100 py-2 px-4 rounded-lg border">
                        <span class="text-sm text-gray-600">Proforma Invoice Number:</span>
                        <span id="invoice-number-display" class="text-lg font-bold text-gray-800 ml-2">Generating...</span>
                    </div>
                </div>
                
                <!-- Client and Transaction Details -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-700">
                    <div>
                        <p><strong>Entity:</strong> {{ auth()->user()->business->name ?? 'N/A' }}</p>
                        <p><strong>Date:</strong> {{ now()->format('n/j/Y') }}</p>
                        <p><strong>Attended To By:</strong> {{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <p><strong>Client Name:</strong> {{ $client->name }}</p>
                        <p><strong>Client ID:</strong> {{ $client->client_id }}</p>
                        <p><strong>Visit ID:</strong> {{ $client->visit_id }}</p>
                        <p><strong>Branch Name:</strong> {{ auth()->user()->currentBranch->name ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="border border-gray-300 px-4 py-2 text-left">Item</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Type</th>
                                <th class="border border-gray-300 px-4 py-2 text-center">Quantity</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Price</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items-table">
                            <!-- Items will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                
                <!-- Package Tracking Numbers Summary -->
                <div id="package-tracking-summary" class="mb-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Package Tracking Numbers</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div id="package-tracking-list" class="space-y-2">
                            <!-- Package tracking numbers will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 mt-6">
                    <button onclick="closeInvoicePreview()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                    <button id="export-tracking-btn" onclick="exportPackageTrackingNumbers()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors hidden">
                        Export Tracking Numbers
                    </button>
                    <button onclick="confirmAndSaveInvoice()" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        Confirm & Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Modal -->
    <div id="payment-methods-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Payment Methods</h3>
                    <button onclick="closePaymentMethodsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">Select payment methods in order of preference:</p>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="insurance" 
                                   {{ in_array('insurance', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏥 Insurance</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="credit_arrangement_institutions" 
                                   {{ in_array('credit_arrangement_institutions', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏦 Credit Arrangement Institutions</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="mobile_money" 
                                   {{ in_array('mobile_money', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">📱 Mobile Money</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="v_card" 
                                   {{ in_array('v_card', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">💳 V Card (Virtual Card)</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="p_card" 
                                   {{ in_array('p_card', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">💳 P Card (Physical Card)</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="bank_transfer" 
                                   {{ in_array('bank_transfer', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">🏦 Bank Transfer</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="payment_methods[]" value="cash" 
                                   {{ in_array('cash', $client->payment_methods ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm font-medium text-gray-700">💵 Cash (if enabled)</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closePaymentMethodsModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button onclick="savePaymentMethods()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
