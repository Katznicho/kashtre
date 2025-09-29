{{-- Unified Make a Request/Order Component --}}
{{-- This component serves as the single source of truth for both service points and POS --}}

<!-- Section 4: Make a Request/Order - Professional Two Column Layout -->
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
                                            
                                            // Add variation descriptions
                                            if (str_contains($itemName, 'advanced') || str_contains($itemName, 'premium') || str_contains($itemName, 'deluxe') || str_contains($itemName, 'professional') || str_contains($itemName, 'enhanced')) {
                                                $descriptionParts[] = 'Premium quality variant';
                                            } elseif (str_contains($itemName, 'basic') || str_contains($itemName, 'standard') || str_contains($itemName, 'regular')) {
                                                $descriptionParts[] = 'Standard quality variant';
                                            } elseif (str_contains($itemName, 'economy') || str_contains($itemName, 'budget') || str_contains($itemName, 'value')) {
                                                $descriptionParts[] = 'Economy quality variant';
                                            }
                                            
                                            // Add additional properties
                                            if ($item->category && !empty(trim($item->category))) {
                                                $descriptionParts[] = "Category: {$item->category}";
                                            }
                                            
                                            if ($item->other_names && !empty(trim($item->other_names))) {
                                                $descriptionParts[] = "Also known as: {$item->other_names}";
                                            }
                                            
                                            if ($item->unit && !empty(trim($item->unit))) {
                                                $descriptionParts[] = "Unit: {$item->unit}";
                                            }
                                            
                                            $description = implode(' • ', $descriptionParts);
                                        }
                                    @endphp
                                    @if($description)
                                    <p class="text-xs text-gray-500 mt-1 description-display">{{ $description }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1 price-display">
                                        @if(isset($item->final_price) && $item->final_price != $item->default_price)
                                            UGX {{ number_format($item->final_price ?? 0, 2) }} <span class="text-green-600">(Branch Price)</span>
                                        @else
                                            UGX {{ number_format($item->default_price ?? 0, 0) }} <span class="text-gray-500">(Default Price)</span>
                                        @endif
                                        @if($item->vat_rate && $item->vat_rate > 0)
                                            <span class="text-orange-600">(VAT: {{ $item->vat_rate }}%)</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <input type="number" 
                                           min="0" 
                                           step="1"
                                           value="0"
                                           data-item-id="{{ $item->id }}"
                                           data-item-name="{{ $item->display_name }}"
                                           data-item-type="{{ $item->type ?? 'good' }}"
                                           data-item-price="{{ $item->final_price ?? $item->default_price ?? 0 }}"
                                           class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                           placeholder="0">
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-8 text-center text-gray-500">
                            <p>No items available for this business.</p>
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
                
                <!-- Selected Items Table -->
                <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <div class="grid grid-cols-5 gap-4 text-sm font-medium text-gray-700">
                            <div>Item</div>
                            <div>Type</div>
                            <div>Quantity</div>
                            <div>Price</div>
                            <div>Action</div>
                        </div>
                    </div>
                    
                    <div id="selected-items-container" class="divide-y divide-gray-200 min-h-48">
                        <div class="px-4 py-8 text-center text-gray-500">
                            No items selected
                        </div>
                    </div>
                </div>
                
                <!-- Order Totals -->
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Unique Items:</span>
                            <span class="text-sm font-medium text-gray-900" id="unique-items-count">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Quantity:</span>
                            <span class="text-sm font-medium text-gray-900" id="total-quantity">0</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <span class="text-sm font-medium text-gray-900">Total Amount:</span>
                            <span class="text-sm font-medium text-gray-900" id="total-amount">UGX 0.00</span>
                        </div>
                    </div>
                </div>
                
                <!-- Package Adjustment Details -->
                <div id="package-adjustment-details" class="mb-4 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Package Adjustments Applied</h3>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div id="package-adjustment-list" class="space-y-2">
                            <!-- Package adjustment details will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Financial Summary -->
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="space-y-2 text-sm">
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
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <span class="font-medium">Subtotal 2:</span>
                            <span id="invoice-subtotal-2" class="font-medium">UGX 0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Service Charge:</span>
                            <span id="service-charge-display">UGX 0.00</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold text-lg">
                            <span>Total Amount:</span>
                            <span id="invoice-total">UGX 0.00</span>
                        </div>
                        <div class="text-xs text-gray-500 text-right italic" id="service-charge-note">
                            No charges for this amount range
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total:</span>
                            <span id="invoice-final-total">UGX 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Preview Proforma Invoice Button -->
                <button onclick="showInvoicePreview()" class="w-full bg-gray-900 text-white py-3 px-4 rounded-lg hover:bg-gray-800 transition-colors font-medium">
                    Preview Proforma Invoice
                </button>
            </div>
        </div>
    </div>
</div>
