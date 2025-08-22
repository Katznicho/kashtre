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
                    View All Invoices
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
                            <p class="text-sm text-gray-500 mb-1">Current Balance</p>
                            <p class="text-xl font-bold text-gray-900">UGX {{ number_format($client->balance ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Transactions</p>
                            <p class="text-xl font-bold text-yellow-600">0</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            View Detailed Client Statement
                        </button>
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
            
            <!-- Section 4: Make a Request/Order - Professional Two Column Layout -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Make a Request/Order</h3>
                    
                    <!-- Main POS Interface - Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Left Column: Item Selection -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Select Item</h4>
                            
                            <!-- Search and Filter Bar -->
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="flex-1 relative">
                                    <input type="text" id="search-input" placeholder="Search" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="show-prices" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="text-sm text-gray-700">Show Prices</span>
                                </label>
                                <button class="p-2 text-gray-400 hover:text-gray-600 border border-gray-300 rounded-md">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </button>
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
                                    <div class="item-row px-4 py-3 hover:bg-gray-50" data-item-name="{{ strtolower($item->name) }}" data-item-other-names="{{ strtolower($item->other_names ?? '') }}">
                                        <div class="grid grid-cols-2 gap-4 items-center">
                                            <div>
                                                <span class="text-sm text-gray-900">{{ $item->name }}</span>
                                                @if($item->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ $item->description }}</p>
                                                @endif
                                                @if($item->other_names)
                                                <p class="text-xs text-gray-600 mt-1">Other Names: {{ $item->other_names }}</p>
                                                @endif
                                                <p class="text-xs text-blue-600 mt-1 price-display">
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
                                                       data-item-name="{{ $item->name }}">
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
                        
                        <!-- Right Column: Receipt -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Receipt</h4>
                            
                            <!-- Receipt Table -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <div class="grid grid-cols-4 gap-4">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Item</span>
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
                                
                                <div id="receipt-items" class="divide-y divide-gray-200 min-h-32">
                                    <div class="px-4 py-8">
                                        <p class="text-sm text-gray-500 text-center">No items selected</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Receipt Summary -->
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
                                    Preview Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 5: Ordered Items (Requests/Orders) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ordered Items (Requests/Orders)</h3>
                    <div class="border border-gray-200 rounded-lg">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="grid grid-cols-4 gap-4">
                                <div><span class="text-sm font-medium text-gray-700">Item</span></div>
                                <div><span class="text-sm font-medium text-gray-700">Quantity</span></div>
                                <div><span class="text-sm font-medium text-gray-700">Amount</span></div>
                                <div><span class="text-sm font-medium text-gray-700">Status</span></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-gray-500 text-center">No orders found for this client</p>
                        </div>
                    </div>
                </div>
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

    <!-- Invoice Preview Modal -->
    <div id="invoice-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Invoice Header -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Invoice Preview</h2>
                    <div class="bg-blue-600 text-white py-2 px-4 rounded-lg">
                        <span class="text-lg font-semibold">Pro Invoice</span>
                    </div>
                </div>
                
                <!-- Client and Transaction Details -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-700">
                    <div>
                        <p><strong>Payment Phone:</strong> {{ $client->payment_phone_number ?? 'N/A' }}</p>
                        <p><strong>Client:</strong> {{ $client->name }} {{ $client->client_id }}</p>
                        <p><strong>Visit ID:</strong> {{ $client->visit_id }}</p>
                        <p><strong>Branch ID:</strong> {{ auth()->user()->currentBranch->id ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p><strong>Date:</strong> {{ now()->format('n/j/Y') }}</p>
                        <p><strong>Hospital:</strong> {{ auth()->user()->business->name ?? 'N/A' }}</p>
                        <p><strong>Attended To By:</strong> {{ auth()->user()->name }} {{ auth()->user()->business->name ?? '' }}</p>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="border border-gray-300 px-4 py-2 text-left">Item</th>
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
                
                <!-- Financial Summary -->
                <div class="text-right space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span id="invoice-subtotal">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Package Adjustment:</span>
                        <span id="package-adjustment-display">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Account Balance(A/c) Adjustment:</span>
                        <span>UGX 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Amount Due:</span>
                        <span id="invoice-amount-due">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Service Charge:</span>
                        <span id="service-charge-display">UGX 0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Final Total:</span>
                        <span id="invoice-final-total">UGX 0.00</span>
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
                    <button onclick="printInvoice()" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        Print Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
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
                    const rawPrice = this.dataset.itemPrice;
                    const itemPrice = parseFloat(rawPrice) || 0;
                    const quantity = parseInt(this.value) || 0;
                    
                    // Debug logging
                    console.log('Item:', itemName, 'Raw Price:', rawPrice, 'Parsed Price:', itemPrice, 'Quantity:', quantity);
                    
                    if (quantity > 0) {
                        addToCart(itemId, itemName, itemPrice, quantity);
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
                    
                    // Search in both item name and other names, but return entries in the items field
                    if (itemName.includes(searchTerm) || itemOtherNames.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Add price display toggle functionality
            const showPricesCheckbox = document.getElementById('show-prices');
            const priceElements = document.querySelectorAll('.price-display');
            
            showPricesCheckbox.addEventListener('change', function() {
                priceElements.forEach(element => {
                    element.style.display = this.checked ? 'block' : 'none';
                });
            });
        });
        
        function addToCart(itemId, itemName, itemPrice, quantity) {
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
                    price: price,
                    quantity: qty
                });
            }
            
            updateReceiptDisplay();
        }
        
        function removeFromCartByItemId(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateReceiptDisplay();
        }
        
        function updateReceiptDisplay() {
            const receiptContainer = document.getElementById('receipt-items');
            const totalItemsSpan = document.getElementById('total-items');
            const totalQuantitySpan = document.getElementById('total-quantity');
            const totalAmountSpan = document.getElementById('total-amount');
            
            if (cart.length === 0) {
                receiptContainer.innerHTML = '<div class="px-4 py-8"><p class="text-sm text-gray-500 text-center">No items selected</p></div>';
                totalItemsSpan.textContent = '0';
                totalQuantitySpan.textContent = '0';
                totalAmountSpan.textContent = 'UGX 0.00';
                return;
            }
            
            let receiptHTML = '';
            let totalItems = 0;
            let totalQuantity = 0;
            let totalAmount = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = parseFloat(item.price || 0) * parseInt(item.quantity || 0);
                totalItems += 1; // Count unique items
                totalQuantity += parseInt(item.quantity || 0); // Sum of all quantities
                totalAmount += itemTotal;
                
                receiptHTML += `
                    <div class="px-4 py-3">
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <div>
                                <span class="text-sm text-gray-900 font-medium">${item.name}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-900">${item.quantity}</span>
                            </div>
                            <div>
                                <span class="text-sm text-blue-600">UGX ${(item.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>
                            <div>
                                <button class="text-red-500 hover:text-red-700 text-sm" onclick="removeFromCart(${index})">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            receiptContainer.innerHTML = receiptHTML;
            totalItemsSpan.textContent = totalItems; // This now shows total quantity of all items
            totalQuantitySpan.textContent = totalQuantity; // This now shows total quantity of all items
            totalAmountSpan.textContent = `UGX ${parseFloat(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        function removeFromCart(index) {
            const removedItem = cart[index];
            cart.splice(index, 1);
            
            // Reset the corresponding quantity input to 0
            const quantityInput = document.querySelector(`input[data-item-id="${removedItem.id}"]`);
            if (quantityInput) {
                quantityInput.value = 0;
            }
            
            updateReceiptDisplay();
        }
        
        // Show client confirmation modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            showClientConfirmation();
        });
        
        function showClientConfirmation() {
            document.getElementById('client-confirmation-modal').classList.remove('hidden');
        }
        
        function closeClientConfirmation() {
            document.getElementById('client-confirmation-modal').classList.add('hidden');
        }
        
        function printClientDetails() {
            // Create a print-friendly version of client details
            const printWindow = window.open('', '_blank');
            const modalContent = document.querySelector('#client-confirmation-modal .relative').cloneNode(true);
            
            // Remove action buttons from print version
            const actionButtons = modalContent.querySelector('.bg-gray-50.px-6.py-4.rounded-b-lg');
            if (actionButtons) {
                actionButtons.remove();
            }
            
            // Add print styles
            const printStyles = `
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .bg-gray-50 { background-color: #f9fafb; }
                    .text-gray-800 { color: #1f2937; }
                    .text-gray-600 { color: #4b5563; }
                    .text-sm { font-size: 14px; }
                    .text-lg { font-size: 18px; }
                    .font-semibold { font-weight: 600; }
                    .text-center { text-align: center; }
                    .grid { display: grid; }
                    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
                    .gap-4 { gap: 16px; }
                    .space-y-2 > * + * { margin-top: 8px; }
                    .px-6 { padding-left: 24px; padding-right: 24px; }
                    .py-4 { padding-top: 16px; padding-bottom: 16px; }
                    .mb-4 { margin-bottom: 16px; }
                    .rounded-lg { border-radius: 8px; }
                    .border-b { border-bottom: 1px solid #e5e7eb; }
                    .w-16 { width: 64px; }
                    .h-16 { height: 64px; }
                    .bg-gray-200 { background-color: #e5e7eb; }
                    .border { border: 1px solid #d1d5db; }
                    .flex { display: flex; }
                    .justify-end { justify-content: flex-end; }
                    .items-center { align-items: center; }
                    .justify-center { justify-content: center; }
                    .text-xs { font-size: 12px; }
                    .text-gray-500 { color: #6b7280; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Client Details - ${new Date().toLocaleDateString()}</title>
                    ${printStyles}
                </head>
                <body>
                    ${modalContent.outerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Wait for content to load then print
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
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
            
            // Populate invoice items table
            const invoiceTable = document.getElementById('invoice-items-table');
            let tableHTML = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = (item.price || 0) * (item.quantity || 0);
                subtotal += itemTotal;
                
                tableHTML += `
                    <tr class="bg-white">
                        <td class="border border-gray-300 px-4 py-2">${item.name}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">${item.quantity}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${(item.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">UGX ${itemTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
            
            invoiceTable.innerHTML = tableHTML;
            
            // Calculate package adjustment
            const packageAdjustment = await calculatePackageAdjustment();
            
            // Calculate totals with dynamic service charge
            const serviceCharge = await calculateServiceCharge(subtotal);
            const adjustedSubtotal = parseFloat(subtotal) - parseFloat(packageAdjustment);
            const finalTotal = parseFloat(adjustedSubtotal) + parseFloat(serviceCharge);
            
            // Update invoice summary
            document.getElementById('invoice-subtotal').textContent = `UGX ${parseFloat(subtotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('package-adjustment-display').textContent = `UGX ${parseFloat(packageAdjustment).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('service-charge-display').textContent = `UGX ${parseFloat(serviceCharge).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('invoice-amount-due').textContent = `UGX ${parseFloat(adjustedSubtotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('invoice-final-total').textContent = `UGX ${parseFloat(finalTotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Show modal
            document.getElementById('invoice-modal').classList.remove('hidden');
        }
        
        function closeInvoicePreview() {
            document.getElementById('invoice-modal').classList.add('hidden');
        }
        
        async function confirmAndSaveInvoice() {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Cart',
                    text: 'Please add items to cart before saving invoice'
                });
                return;
            }
            
            // Confirm with SweetAlert2
            const result = await Swal.fire({
                title: 'Confirm Invoice',
                text: 'Are you sure you want to save this invoice?',
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
                cart.forEach(item => {
                    subtotal += parseFloat(item.price || 0) * parseInt(item.quantity || 0);
                });
                
                const packageAdjustment = await calculatePackageAdjustment();
                const serviceCharge = await calculateServiceCharge(subtotal);
                const adjustedSubtotal = parseFloat(subtotal) - parseFloat(packageAdjustment);
                const totalAmount = parseFloat(adjustedSubtotal) + parseFloat(serviceCharge);
                
                // Get payment phone and methods
                const paymentPhone = document.getElementById('payment-phone-edit')?.value || '';
                const paymentMethods = Array.from(document.querySelectorAll('input[name="payment_methods[]"]:checked'))
                    .map(cb => cb.value);
                
                // Check if mobile money is selected and process payment
                let paymentResult = null;
                let amountPaid = 0;
                
                if (paymentMethods.includes('mobile_money') && paymentPhone) {
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
                            timer: 3000,
                            showConfirmButton: false
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
                }
                
                // Prepare cart items with total_amount for each item
                const itemsWithTotals = cart.map(item => ({
                    ...item,
                    total_amount: parseFloat(item.price || 0) * parseInt(item.quantity || 0)
                }));
                
                // Prepare invoice data with all required fields
                const invoiceData = {
                    client_id: {{ $client->id }},
                    business_id: {{ auth()->user()->business->id }},
                    branch_id: {{ auth()->user()->currentBranch->id ?? 'null' }},
                    created_by: {{ auth()->id() }},
                    client_name: '{{ $client->name }}',
                    client_phone: '{{ $client->phone_number }}',
                    payment_phone: paymentPhone,
                    visit_id: '{{ $client->visit_id }}',
                    items: itemsWithTotals,
                    subtotal: subtotal,
                    package_adjustment: packageAdjustment,
                    account_balance_adjustment: 0,
                    service_charge: serviceCharge,
                    total_amount: totalAmount,
                    amount_paid: amountPaid,
                    balance_due: totalAmount - amountPaid,
                    payment_methods: paymentMethods,
                    payment_status: amountPaid >= totalAmount ? 'paid' : 'pending',
                    status: 'confirmed',
                    notes: ''
                };
                
                // Save invoice
                const response = await fetch('/invoices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(invoiceData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear cart
                    cart = [];
                    updateReceiptDisplay();
                    
                    // Close invoice preview
                    closeInvoicePreview();
                    
                    // Show success with options
                    const result = await Swal.fire({
                        icon: 'success',
                        title: 'Invoice Saved!',
                        text: 'Invoice has been saved successfully.',
                        showCancelButton: true,
                        confirmButtonText: 'View Invoice',
                        cancelButtonText: 'Print Invoice',
                        showDenyButton: true,
                        denyButtonText: 'Stay Here'
                    });
                    
                    if (result.isConfirmed) {
                        // View invoice
                        window.location.href = `/invoices/${data.invoice.id}`;
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Print invoice
                        window.open(`/invoices/${data.invoice.id}/print`, '_blank');
                    }
                    // If "Stay Here" is clicked, do nothing
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error saving invoice: ' + (data.message || 'Unknown error')
                    });
                }
                
            } catch (error) {
                console.error('Error saving invoice:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error saving invoice. Please try again.'
                });
            } finally {
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
            }
        }
        
        function printInvoice() {
            // Create a print-friendly version
            const printWindow = window.open('', '_blank');
            const modalContent = document.querySelector('#invoice-modal .relative').cloneNode(true);
            
            // Remove action buttons from print version
            const actionButtons = modalContent.querySelector('.flex.justify-end.space-x-4');
            if (actionButtons) {
                actionButtons.remove();
            }
            
            // Add print styles
            const printStyles = `
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #2563eb; color: white; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .text-left { text-align: left; }
                    .font-bold { font-weight: bold; }
                    .bg-blue-600 { background-color: #2563eb; }
                    .text-white { color: white; }
                    .py-2 { padding-top: 8px; padding-bottom: 8px; }
                    .px-4 { padding-left: 16px; padding-right: 16px; }
                    .rounded-lg { border-radius: 8px; }
                    .mb-6 { margin-bottom: 24px; }
                    .space-y-2 > * + * { margin-top: 8px; }
                    .border-t { border-top: 1px solid #ddd; }
                    .pt-2 { padding-top: 8px; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Invoice - ${new Date().toLocaleDateString()}</title>
                    ${printStyles}
                </head>
                <body>
                    ${modalContent.outerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Wait for content to load then print
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        
        // Payment Methods Modal Functions
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
                    updatePaymentMethodsDisplay(selectedMethods);
                    closePaymentMethodsModal();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment methods updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error updating payment methods: ' + data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error updating payment methods'
                });
            });
        }
        
        function updatePaymentMethodsDisplay(methods) {
            const container = document.querySelector('.payment-methods-display');
            if (methods.length > 0) {
                container.innerHTML = methods.map((method, index) => 
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${index + 1}. ${method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>`
                ).join('');
            } else {
                container.innerHTML = '<span class="text-sm text-gray-500">No payment methods specified</span>';
            }
            
            // Show/hide payment phone section based on mobile money selection
            const paymentPhoneSection = document.getElementById('payment-phone-section');
            if (methods.includes('mobile_money')) {
                paymentPhoneSection.style.display = 'block';
            } else {
                paymentPhoneSection.style.display = 'none';
            }
        }
        
        async function processMobileMoneyPayment(amount, phoneNumber) {
            // Simulate mobile money payment processing
            return new Promise((resolve) => {
                setTimeout(() => {
                    // Simulate successful payment
                    resolve({
                        success: true,
                        transaction_id: 'MM_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        amount: amount,
                        phone: phoneNumber,
                        status: 'success',
                        message: 'Payment processed successfully'
                    });
                }, 2000); // Simulate 2 second processing time
            });
        }
        
        async function calculateServiceCharge(subtotal) {
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
                        business_id: {{ auth()->user()->business->id }},
                        branch_id: {{ auth()->user()->currentBranch->id ?? 'null' }}
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    return parseFloat(data.service_charge) || 0;
                } else {
                    console.error('Service charge calculation error:', data.message);
                    return 0;
                }
            } catch (error) {
                console.error('Error calculating service charge:', error);
                return 0;
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
                        client_id: {{ $client->id }},
                        business_id: {{ auth()->user()->business->id }},
                        items: cart
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    return parseFloat(data.total_adjustment) || 0;
                } else {
                    console.error('Package adjustment calculation error:', data.message);
                    return 0;
                }
            } catch (error) {
                console.error('Error calculating package adjustment:', error);
                return 0;
            }
        }
        
        function savePaymentPhone() {
            const phoneInput = document.getElementById('payment-phone-edit');
            const phoneNumber = phoneInput.value.trim();
            const button = event.target;
            const originalText = button.textContent;
            
            // Show loading state
            button.textContent = 'Saving...';
            button.disabled = true;
            
            fetch(`/clients/{{ $client->id }}/update-payment-phone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_phone_number: phoneNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment phone number updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error updating payment phone number: ' + data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error updating payment phone number'
                });
            })
            .finally(() => {
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
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
