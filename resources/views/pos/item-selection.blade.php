<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Page - ') }}{{ $client->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Client ID: {{ $client->client_id }}</span>
                <span class="text-sm text-gray-600">Visit ID: {{ $client->visit_id }}</span>
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                    Active
                </span>
                <a href="/invoices" class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    View All Proforma Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif
            
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
                            <p class="text-xl font-bold text-yellow-600">0</p>
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            View Detailed Client Statement
                        </button>
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
                            <textarea placeholder="Add notes about this client..." class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"></textarea>
                            <div class="mt-3 flex justify-end">
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    Save Notes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Unified Make a Request/Order Component --}}
            <x-pos.make-request-order :items="$items" />
            
            <!-- Section 5: Ordered Items (Requests/Orders) -->
            <x-pos.ordered-items 
                :pendingItems="$pendingItems" 
                :partiallyDoneItems="$partiallyDoneItems" 
                :completedItems="collect()" 
                :correctTotalAmount="$correctTotalAmount ?? 0" 
            />
            
            <!-- Save and Exit Button -->
            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('clients.index') }}" 
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
    
    <!-- Client Confirmation Modal -->
    <div id="client-confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <!-- Header -->
            <div class="bg-gray-50 px-6 py-4 rounded-t-lg border-b">
                <h3 class="text-lg font-semibold text-gray-800 text-center">
                    {{ auth()->user()->business->name ?? 'Medical Centre' }}
                </h3>
            </div>
            
            <!-- Client Details -->
            <div class="px-6 py-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">{{ $client->name }}</p>
                        <p class="text-sm text-gray-600">Client ID: {{ $client->client_id }}</p>
                        <p class="text-sm text-gray-600">Branch: {{ auth()->user()->currentBranch->name ?? 'N/A' }}</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">Age: {{ $client->age ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Sex: {{ $client->sex ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Visit ID: {{ $client->visit_id }}</p>
                    </div>
                </div>
                
                <!-- QR Code Placeholder -->
                <div class="flex justify-end mb-4">
                    <div class="w-16 h-16 bg-white border border-gray-300 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=64x64&data={{ urlencode($client->client_id . '|' . $client->name) }}" 
                             alt="QR Code" 
                             class="w-full h-full object-contain"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="hidden w-full h-full bg-gray-100 border border-gray-300 flex items-center justify-center">
                            <span class="text-xs text-gray-500 text-center">QR<br>Code</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-center space-x-4">
                <button onclick="printClientDetails()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition duration-200">
                    Print
                </button>
                <button onclick="closeClientConfirmation()" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

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
                
                <!-- Package Adjustment Details -->
                <div id="package-adjustment-details" class="mb-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Package Adjustments Applied</h3>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div id="package-adjustment-list" class="space-y-2">
                            <!-- Package adjustment details will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Financial Summary -->
                <div class="text-right space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal 1:</span>
                        <span id="invoice-subtotal">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Package Adjustment:</span>
                        <span id="package-adjustment-display">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Account Balance(A/c) Adjustment:</span>
                        <span id="balance-adjustment-display">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Subtotal 2:</span>
                        <span id="invoice-subtotal-2">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Service Charge:</span>
                        <span id="service-charge-display">UGX 0.00</span>
                    </div>
                    <div class="text-xs text-gray-500 text-right italic" id="service-charge-note">
                        No charges for this amount range
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Total:</span>
                        <span id="invoice-final-total">UGX 0.00</span>
                    </div>
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
                    <button onclick="confirmAndSaveInvoice()" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        Confirm & Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // POS Functionality
        let selectedItems = [];
        let showPrices = false;
        let showDescriptions = false;

        // Initialize POS functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializePOS();
        });

        function initializePOS() {
            // Search functionality
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterItems(this.value);
                });
            }

            // Show Prices checkbox
            const showPricesCheckbox = document.getElementById('show-prices');
            if (showPricesCheckbox) {
                showPricesCheckbox.addEventListener('change', function() {
                    showPrices = this.checked;
                    togglePriceDisplay();
                });
            }

            // Show Descriptions checkbox
            const showDescriptionsCheckbox = document.getElementById('show-descriptions');
            if (showDescriptionsCheckbox) {
                showDescriptionsCheckbox.addEventListener('change', function() {
                    showDescriptions = this.checked;
                    toggleDescriptionDisplay();
                });
            }

            // Quantity input changes
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity-input')) {
                    handleQuantityChange(e.target);
                }
            });
        }

        function filterItems(searchTerm) {
            const items = document.querySelectorAll('.item-row');
            const term = searchTerm.toLowerCase();
            
            items.forEach(item => {
                const itemName = item.dataset.itemName || '';
                const itemDisplayName = item.dataset.itemDisplayName || '';
                const itemOtherNames = item.dataset.itemOtherNames || '';
                
                const matches = itemName.includes(term) || 
                               itemDisplayName.toLowerCase().includes(term) || 
                               itemOtherNames.includes(term);
                
                item.style.display = matches ? 'block' : 'none';
            });
        }

        function togglePriceDisplay() {
            const priceElements = document.querySelectorAll('.price-display');
            priceElements.forEach(element => {
                element.style.display = showPrices ? 'block' : 'none';
            });
        }

        function toggleDescriptionDisplay() {
            const descriptionElements = document.querySelectorAll('.description-display');
            descriptionElements.forEach(element => {
                element.style.display = showDescriptions ? 'block' : 'none';
            });
        }

        function handleQuantityChange(input) {
            const itemId = input.dataset.itemId;
            const itemName = input.dataset.itemName;
            const itemType = input.dataset.itemType;
            const itemPrice = parseFloat(input.dataset.itemPrice);
            const quantity = parseInt(input.value) || 0;

            console.log('Quantity changed:', { itemId, itemName, itemType, itemPrice, quantity });

            if (quantity > 0) {
                addItemToOrder(itemId, itemName, itemType, itemPrice, quantity);
            } else {
                removeItemFromOrder(itemId);
            }
            
            updateOrderSummary();
            console.log('Current selectedItems:', selectedItems);
        }

        function addItemToOrder(itemId, itemName, itemType, itemPrice, quantity) {
            // Ensure itemId is a string for consistent comparison
            const stringItemId = String(itemId);
            const existingItemIndex = selectedItems.findIndex(item => String(item.id) === stringItemId);
            
            if (existingItemIndex !== -1) {
                selectedItems[existingItemIndex].quantity = quantity;
                selectedItems[existingItemIndex].totalAmount = itemPrice * quantity;
            } else {
                selectedItems.push({
                    id: stringItemId,
                    name: itemName,
                    type: itemType,
                    price: itemPrice,
                    quantity: quantity,
                    totalAmount: itemPrice * quantity
                });
            }
        }

        function removeItemFromOrder(itemId) {
            const stringItemId = String(itemId);
            selectedItems = selectedItems.filter(item => String(item.id) !== stringItemId);
        }

        function updateOrderSummary() {
            const container = document.getElementById('selected-items-container');
            const uniqueItemsSpan = document.getElementById('unique-items-count');
            const totalQuantitySpan = document.getElementById('total-quantity');
            const totalAmountSpan = document.getElementById('total-amount');

            if (selectedItems.length === 0) {
                container.innerHTML = '<div class="px-4 py-8 text-center text-gray-500">No items selected</div>';
                uniqueItemsSpan.textContent = '0';
                totalQuantitySpan.textContent = '0';
                totalAmountSpan.textContent = 'UGX 0.00';
                return;
            }

            let html = '';
            let totalQuantity = 0;
            let totalAmount = 0;

            selectedItems.forEach(item => {
                totalQuantity += item.quantity;
                totalAmount += item.totalAmount;
                
                html += `
                    <div class="px-4 py-3 grid grid-cols-5 gap-4 items-center">
                        <div class="text-sm text-gray-900">${item.name}</div>
                        <div class="text-sm text-gray-600">${item.type}</div>
                        <div class="text-sm text-gray-900">${item.quantity}</div>
                        <div class="text-sm text-gray-900">UGX ${item.price.toLocaleString()}</div>
                        <div>
                            <button onclick="removeItem('${item.id}')" class="text-red-600 hover:text-red-800 text-sm underline">
                                Remove
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            uniqueItemsSpan.textContent = selectedItems.length;
            totalQuantitySpan.textContent = totalQuantity;
            totalAmountSpan.textContent = `UGX ${totalAmount.toLocaleString()}`;
        }

        function removeItem(itemId) {
            console.log('Removing item with ID:', itemId);
            
            // Reset the quantity input to 0
            const input = document.querySelector(`input[data-item-id="${itemId}"]`);
            if (input) {
                input.value = '0';
                console.log('Reset input for item:', itemId);
            } else {
                console.log('Input not found for item:', itemId);
            }
            
            // Remove from selected items array
            const initialLength = selectedItems.length;
            const stringItemId = String(itemId);
            selectedItems = selectedItems.filter(item => String(item.id) !== stringItemId);
            console.log(`Removed item. Array length: ${initialLength} -> ${selectedItems.length}`);
            
            // Update the order summary
            updateOrderSummary();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Item Removed',
                text: 'Item has been removed from your order.',
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Initialize package tracking numbers storage
        window.packageTrackingNumbers = new Map();
        
        // Generate package tracking number
        function generatePackageTrackingNumber(packageId, packageName) {
            if (window.packageTrackingNumbers.has(packageId)) {
                return window.packageTrackingNumbers.get(packageId);
            }
            
            // Generate a unique tracking number
            const timestamp = Date.now().toString().slice(-6);
            const randomSuffix = Math.random().toString(36).substring(2, 5).toUpperCase();
            const packagePrefix = packageName ? packageName.substring(0, 3).toUpperCase() : 'PKG';
            const trackingNumber = `${packagePrefix}-${timestamp}-${randomSuffix}`;
            
            // Store the tracking number
            window.packageTrackingNumbers.set(packageId, trackingNumber);
            
            return trackingNumber;
        }

        // Preview Proforma Invoice functionality - Full POS functionality
        document.addEventListener('click', function(e) {
            if (e.target.textContent === 'Preview Proforma Invoice') {
                if (selectedItems.length === 0) {
                    Swal.fire({
                        title: 'No Items Selected',
                        text: 'Please select at least one item before previewing the invoice.',
                        icon: 'warning',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                showInvoicePreview();
            }
        });

        async function showInvoicePreview() {
            console.log('showInvoicePreview called');
            console.log('selectedItems:', selectedItems);
            
            if (selectedItems.length === 0) {
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
            
            selectedItems.forEach(item => {
                const itemTotal = (item.price || 0) * (item.quantity || 0);
                subtotal += itemTotal;
                
                // Generate tracking number for packages
                let trackingNumber = 'N/A';
                if (item.type === 'package') {
                    trackingNumber = generatePackageTrackingNumber(item.id, item.name);
                }
                
                tableHTML += `
                    <tr class="bg-white">
                        <td class="border border-gray-300 px-4 py-2">${item.name}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">${item.type || 'N/A'}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">${item.quantity}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${(item.price || 0).toLocaleString()}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${itemTotal.toLocaleString()}</td>
                    </tr>
                `;
            });
            
            invoiceTable.innerHTML = tableHTML;
            
            // Calculate package adjustment
            const packageAdjustmentData = await calculatePackageAdjustment();
            const packageAdjustment = parseFloat(packageAdjustmentData.total_adjustment) || 0;
            
            // Show package adjustment details if any adjustments were made
            if (packageAdjustmentData.details && packageAdjustmentData.details.length > 0) {
                const adjustmentDetailsContainer = document.getElementById('package-adjustment-details');
                const adjustmentList = document.getElementById('package-adjustment-list');
                
                let detailsHTML = '';
                packageAdjustmentData.details.forEach(detail => {
                    // Generate tracking number for package adjustments
                    const packageTrackingNumber = generatePackageTrackingNumber(detail.package_id || 'adj_' + Date.now(), detail.package_name);
                    
                    detailsHTML += `
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <span class="font-medium text-gray-800">${detail.item_name}</span>
                                <span class="text-gray-600"> (${detail.quantity_adjusted} × UGX ${(detail.adjustment_amount / detail.quantity_adjusted).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})})</span>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-green-800">-UGX ${detail.adjustment_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                <div class="text-xs text-gray-500">From: ${detail.package_name}</div>
                                <div class="text-xs text-blue-600 font-mono">${packageTrackingNumber}</div>
                            </div>
                        </div>
                    `;
                });
                
                adjustmentList.innerHTML = detailsHTML;
                adjustmentDetailsContainer.classList.remove('hidden');
            } else {
                document.getElementById('package-adjustment-details').classList.add('hidden');
            }
            
            // Show package tracking summary if there are packages in the cart
            const packagesInCart = selectedItems.filter(item => item.type === 'package');
            if (packagesInCart.length > 0) {
                const trackingSummaryContainer = document.getElementById('package-tracking-summary');
                const trackingList = document.getElementById('package-tracking-list');
                
                let trackingHTML = '';
                packagesInCart.forEach(package => {
                    const trackingNumber = window.packageTrackingNumbers.get(package.id) || generatePackageTrackingNumber(package.id, package.name);
                    trackingHTML += `
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <span class="font-medium text-gray-800">${package.name}</span>
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
            console.log('=== POS ITEM SELECTION - SAVE AND EXIT TRIGGERED ===', {
                client_id: {{ $client->id }},
                client_name: '{{ $client->name }}',
                page: 'POS Item Selection',
                action: 'Save and Exit',
                unified_component_used: true,
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
                    console.log('=== POS ITEM SELECTION - SAVE CONFIRMED ===', {
                        client_id: {{ $client->id }},
                        action: 'Save Confirmed',
                        redirect_to: 'clients.index',
                        timestamp: new Date().toISOString()
                    });

                    Swal.fire({
                        title: 'Saved Successfully!',
                        text: 'Changes have been saved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Redirect back to clients list
                        window.location.href = '{{ route("clients.index") }}';
                    });
                } else {
                    console.log('=== POS ITEM SELECTION - SAVE CANCELLED ===', {
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
