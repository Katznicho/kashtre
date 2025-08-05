
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Create New Item</h2>

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('items.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($canSelectBusiness)
                        <!-- Business Selection (only for business_id == 1) -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business <span class="text-red-500">*</span></label>
                            <select name="business_id" id="business_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                <option value="" disabled>Select business</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id', $selectedBusinessId) == $business->id ? 'selected' : '' }}>{{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <!-- Hidden business field for non-admin users -->
                        <input type="hidden" name="business_id" value="{{ Auth::user()->business_id }}">
                        @endif

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item name" value="{{ old('name') }}">
                        </div>

                        <!-- Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <div class="mt-1 relative">
                                <input type="text" name="code" id="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-gray-100 dark:bg-gray-800" placeholder="Auto-generated code" value="{{ old('code') }}" readonly>
                                <button type="button" id="generate_code_btn" class="absolute inset-y-0 right-0 px-3 flex items-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500 border-l border-gray-300 dark:border-gray-500 rounded-r-md">
                                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Leave empty for auto-generation or click the refresh button to generate a new code</p>
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type <span class="text-red-500">*</span></label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                <option value="" disabled selected>Select type</option>
                                <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service</option>
                                <option value="good" {{ old('type') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="package" {{ old('type') == 'package' ? 'selected' : '' }}>Package</option>
                                <option value="bulk" {{ old('type') == 'bulk' ? 'selected' : '' }}>Bulk</option>
                            </select>
                        </div>

                        <!-- Default Price -->
                        <div>
                            <label for="default_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Price <span class="text-red-500">*</span></label>
                            <input type="number" name="default_price" id="default_price" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="0.00" value="{{ old('default_price') }}">
                        </div>

                        <!-- Hospital Share -->
                        <div>
                            <label for="hospital_share" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hospital Share (%) <span class="text-red-500">*</span></label>
                            <input type="number" name="hospital_share" id="hospital_share" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required value="{{ old('hospital_share', 100) }}" placeholder="100">
                            <p class="mt-1 text-sm text-gray-500">If less than 100%, a Destination Account must be selected</p>
                        </div>

                        <!-- Validity Days (for package items) -->
                        <div id="validity_days_div" style="display: none;">
                            <label for="validity_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Validity Period (Days) <span class="text-red-500">*</span></label>
                            <input type="number" name="validity_days" id="validity_days" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ old('validity_days', 30) }}" placeholder="30">
                            <p class="mt-1 text-sm text-gray-500">Number of days the package is valid after purchase</p>
                        </div>

                        <!-- Group -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group <span class="text-red-500">*</span></label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subgroup -->
                        <div>
                            <label for="subgroup_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subgroup <span class="text-red-500">*</span></label>
                            <select name="subgroup_id" id="subgroup_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('subgroup_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department <span class="text-red-500">*</span></label>
                            <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- UOM -->
                        <div>
                            <label for="uom_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure <span class="text-red-500">*</span></label>
                            <select name="uom_id" id="uom_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($itemUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ old('uom_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Point -->
                        <div>
                            <label for="service_point_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Point <span class="text-red-500">*</span></label>
                            <select name="service_point_id" id="service_point_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($servicePoints as $point)
                                    <option value="{{ $point->id }}" {{ old('service_point_id') == $point->id ? 'selected' : '' }}>{{ $point->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Contractor (shown only when hospital share < 100%) -->
                        <div id="contractor_div" style="display: none;">
                            <label for="contractor_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destination Account <span class="text-red-500">*</span></label>
                            <select name="contractor_account_id" id="contractor_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select Destination Account</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('contractor_account_id') == $contractor->id ? 'selected' : '' }}>
                                        {{ $contractor->account_name }} ({{ $contractor->business->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Other Name -->
                        <div class="md:col-span-2">
                            <label for="other_names" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Name <span class="text-red-500">*</span></label>
                            <input type="text" name="other_names" id="other_names" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter other name" value="{{ old('other_names') }}">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter description">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Branch Pricing Type Selection -->
                    @if(count($branches) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Branch Pricing</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="radio" name="pricing_type" value="default" id="default_pricing" 
                                           {{ old('pricing_type', 'default') == 'default' ? 'checked' : '' }} 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Use default price for all branches</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="pricing_type" value="custom" id="custom_pricing" 
                                           {{ old('pricing_type') == 'custom' ? 'checked' : '' }} 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Set different prices for each branch</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Pricing Section -->
                    <div class="mt-8 branch-pricing-section" id="branch_pricing_section" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Branch-Specific Pricing</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Set different prices for specific branches</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($branches as $branch)
                            <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $branch->name }}
                                </label>
                                <input type="number" 
                                       name="branch_prices[{{ $loop->index }}][branch_id]" 
                                       value="{{ $branch->id }}" 
                                       style="display: none;">
                                <input type="number" 
                                       name="branch_prices[{{ $loop->index }}][price]" 
                                       step="0.01" 
                                       min="0" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                       placeholder="Use default price">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use default price. At least one branch must have a custom price.</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Package Items Section -->
                    <div class="mt-8 package-items-section" id="package_items_section" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Package Items</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select items to include in this package with their maximum quantities and validity periods</p>
                        
                        <div id="package_items_container">
                            <div class="package-item-entry border rounded-lg p-4 bg-gray-50 dark:bg-gray-700 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item</label>
                                        <select name="package_items[0][included_item_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Select Item</option>
                                            @foreach($availableItems as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Quantity</label>
                                        <input type="number" name="package_items[0][max_quantity]" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                </div>
                                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm remove-package-item" style="display: none;">Remove Item</button>
                            </div>
                        </div>
                        <button type="button" id="add_package_item" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Package Item
                        </button>
                    </div>

                    <!-- Bulk Items Section -->
                    <div class="mt-8 bulk-items-section" id="bulk_items_section" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Bulk Items</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select items to include in this bulk with their fixed quantities</p>
                        
                        <div id="bulk_items_container">
                            <div class="bulk-item-entry border rounded-lg p-4 bg-gray-50 dark:bg-gray-700 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item</label>
                                        <select name="bulk_items[0][included_item_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Select Item</option>
                                            @foreach($availableItems as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fixed Quantity</label>
                                        <input type="number" name="bulk_items[0][fixed_quantity]" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                </div>
                                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm remove-bulk-item" style="display: none;">Remove Item</button>
                            </div>
                        </div>
                        <button type="button" id="add_bulk_item" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Bulk Item
                        </button>
                    </div>

                    <!-- Items Details Preview Section -->
                    <div class="mt-8 items-details-section" id="items_details_section" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Items Details Preview</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Preview of selected items and their details</p>
                        
                        <div id="items_details_container" class="space-y-4">
                            <!-- Items details will be populated here dynamically -->
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('items.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            Create Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hospitalShare = document.getElementById('hospital_share');
            const contractorDiv = document.getElementById('contractor_div');
            const contractorSelect = document.getElementById('contractor_account_id');
            const businessSelect = document.getElementById('business_id');
            const defaultPricing = document.getElementById('default_pricing');
            const customPricing = document.getElementById('custom_pricing');
            const branchPricingSection = document.getElementById('branch_pricing_section');
            const codeInput = document.getElementById('code');
            const generateCodeBtn = document.getElementById('generate_code_btn');
            const typeSelect = document.getElementById('type');
            const packageItemsSection = document.getElementById('package_items_section');
            const bulkItemsSection = document.getElementById('bulk_items_section');
            const validityDaysDiv = document.getElementById('validity_days_div');
            const addPackageItemBtn = document.getElementById('add_package_item');
            const addBulkItemBtn = document.getElementById('add_bulk_item');

            function toggleContractor() {
                if (hospitalShare.value !== '100') {
                    contractorDiv.style.display = 'block';
                    contractorSelect.required = true;
                } else {
                    contractorDiv.style.display = 'none';
                    contractorSelect.required = false;
                    contractorSelect.value = '';
                }
            }

            function toggleBranchPricing() {
                if (customPricing.checked) {
                    branchPricingSection.style.display = 'block';
                    // Branch price inputs are optional - only at least one needs to be filled
                    const branchPriceInputs = branchPricingSection.querySelectorAll('input[name*="[price]"]');
                    branchPriceInputs.forEach(input => {
                        input.required = false;
                        input.removeAttribute('data-required');
                    });
                } else {
                    branchPricingSection.style.display = 'none';
                    // Remove required attribute when default pricing is selected
                    const branchPriceInputs = branchPricingSection.querySelectorAll('input[name*="[price]"]');
                    branchPriceInputs.forEach(input => {
                        input.required = false;
                        input.removeAttribute('data-required');
                        input.value = ''; // Clear values when hiding
                    });
                }
            }

            function togglePackageAndBulkSections() {
                const selectedType = typeSelect.value;
                const validityDaysInput = document.getElementById('validity_days');
                const itemsDetailsSection = document.getElementById('items_details_section');
                
                // Hide both sections initially
                packageItemsSection.style.display = 'none';
                bulkItemsSection.style.display = 'none';
                validityDaysDiv.style.display = 'none';
                itemsDetailsSection.style.display = 'none';
                
                // Reset validity days requirement
                if (validityDaysInput) {
                    validityDaysInput.required = false;
                }
                
                // Show appropriate section based on type
                if (selectedType === 'package') {
                    packageItemsSection.style.display = 'block';
                    validityDaysDiv.style.display = 'block';
                    itemsDetailsSection.style.display = 'block';
                    // Make validity days required for packages
                    if (validityDaysInput) {
                        validityDaysInput.required = true;
                    }
                } else if (selectedType === 'bulk') {
                    bulkItemsSection.style.display = 'block';
                    itemsDetailsSection.style.display = 'block';
                }
            }

            function updateFilteredData() {
                if (!businessSelect) return;
                
                const businessId = businessSelect.value;
                if (!businessId) return;

                fetch(`{{ route('items.filtered-data') }}?business_id=${businessId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update groups
                        updateSelect('group_id', data.groups);
                        updateSelect('subgroup_id', data.groups);
                        
                        // Update departments
                        updateSelect('department_id', data.departments);
                        
                        // Update item units
                        updateSelect('uom_id', data.itemUnits);
                        
                        // Update service points
                        updateSelect('service_point_id', data.servicePoints);
                        
                        // Update contractors
                        updateSelect('contractor_account_id', data.contractors);
                        
                        // Update branch pricing section
                        updateBranchPricing(data.branches);
                    })
                    .catch(error => {
                        console.error('Error fetching filtered data:', error);
                    });
            }

            function updateSelect(selectId, data) {
                const select = document.getElementById(selectId);
                if (!select) return;

                // Store current value
                const currentValue = select.value;
                
                // Clear existing options except the first one
                const firstOption = select.querySelector('option');
                select.innerHTML = '';
                if (firstOption) {
                    select.appendChild(firstOption);
                }

                // Add new options
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    
                    // Special handling for contractors to show business name
                    if (selectId === 'contractor_account_id' && item.business) {
                        option.textContent = `${item.account_name} (${item.business.name})`;
                    } else {
                        option.textContent = item.name;
                    }
                    
                    select.appendChild(option);
                });

                // Restore value if it still exists in new options
                if (currentValue && data.some(item => item.id == currentValue)) {
                    select.value = currentValue;
                } else {
                    select.value = '';
                }
            }

            function updateBranchPricing(branches) {
                const branchPricingSection = document.querySelector('.branch-pricing-section');
                if (!branchPricingSection) return;

                const grid = branchPricingSection.querySelector('.grid');
                if (!grid) return;

                // Clear existing branch pricing inputs
                grid.innerHTML = '';

                // Add new branch pricing inputs
                branches.forEach((branch, index) => {
                    const branchDiv = document.createElement('div');
                    branchDiv.className = 'border rounded-lg p-4 bg-gray-50 dark:bg-gray-700';
                    branchDiv.innerHTML = `
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ${branch.name}
                        </label>
                        <input type="number" 
                               name="branch_prices[${index}][branch_id]" 
                               value="${branch.id}" 
                               style="display: none;">
                        <input type="number" 
                               name="branch_prices[${index}][price]" 
                               step="0.01" 
                               min="0" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                               placeholder="Enter price">
                        <p class="mt-1 text-xs text-gray-500">${customPricing && customPricing.checked ? 'Leave empty to use default price. At least one branch must have a custom price.' : 'Leave empty to use default price'}</p>
                    `;
                    grid.appendChild(branchDiv);
                });
            }

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // Check custom pricing validation
                if (customPricing && customPricing.checked) {
                    const branchPriceInputs = branchPricingSection.querySelectorAll('input[name*="[price]"]');
                    let hasCustomPrices = false;
                    
                    branchPriceInputs.forEach(input => {
                        if (input.value.trim()) {
                            hasCustomPrices = true;
                            input.classList.remove('border-red-500');
                        }
                    });
                    
                    if (!hasCustomPrices) {
                        e.preventDefault();
                        alert('At least one branch must have a custom price when custom pricing is selected.');
                        return false;
                    }
                }
                
                // Check validity days requirement for packages
                const selectedType = typeSelect.value;
                const validityDaysInput = document.getElementById('validity_days');
                
                if (selectedType === 'package' && validityDaysInput) {
                    if (!validityDaysInput.value || validityDaysInput.value < 1) {
                        e.preventDefault();
                        alert('Validity Period (Days) is required for package items and must be at least 1 day.');
                        validityDaysInput.focus();
                        return false;
                    }
                }
            });

            // Code generation functionality
            if (generateCodeBtn) {
                generateCodeBtn.addEventListener('click', function() {
                    const businessId = businessSelect ? businessSelect.value : '{{ Auth::user()->business_id }}';
                    if (!businessId) {
                        alert('Please select a business first');
                        return;
                    }
                    
                    fetch(`{{ route('items.generate-code') }}?business_id=${businessId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.code) {
                                codeInput.value = data.code;
                                codeInput.readOnly = false;
                                codeInput.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                                codeInput.classList.add('bg-white', 'dark:bg-gray-700');
                            }
                        })
                        .catch(error => {
                            console.error('Error generating code:', error);
                            alert('Failed to generate code. Please try again.');
                        });
                });
            }

            // Make code input editable on focus
            if (codeInput) {
                codeInput.addEventListener('focus', function() {
                    if (this.readOnly) {
                        this.readOnly = false;
                        this.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                        this.classList.add('bg-white', 'dark:bg-gray-700');
                    }
                });
            }

            // Event listeners
            hospitalShare.addEventListener('input', toggleContractor);
            if (businessSelect) {
                businessSelect.addEventListener('change', updateFilteredData);
            }
            if (defaultPricing) {
                defaultPricing.addEventListener('change', toggleBranchPricing);
            }
            if (customPricing) {
                customPricing.addEventListener('change', toggleBranchPricing);
            }

            // Initial setup
            toggleContractor();
            toggleBranchPricing(); // Initialize branch pricing display
            togglePackageAndBulkSections(); // Initialize package/bulk sections display

            // Package items functionality
            let packageItemIndex = 1;
            addPackageItemBtn.addEventListener('click', function() {
                const container = document.getElementById('package_items_container');
                const newItem = document.createElement('div');
                newItem.className = 'package-item-entry border rounded-lg p-4 bg-gray-50 dark:bg-gray-700 mb-4';
                newItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item</label>
                            <select name="package_items[${packageItemIndex}][included_item_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select Item</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Quantity</label>
                            <input type="number" name="package_items[${packageItemIndex}][max_quantity]" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm remove-package-item">Remove Item</button>
                `;
                container.appendChild(newItem);
                packageItemIndex++;
                
                // Show remove button for first item if we have more than one
                const removeButtons = container.querySelectorAll('.remove-package-item');
                removeButtons.forEach(btn => btn.style.display = 'inline-block');
            });

            // Bulk items functionality
            let bulkItemIndex = 1;
            addBulkItemBtn.addEventListener('click', function() {
                const container = document.getElementById('bulk_items_container');
                const newItem = document.createElement('div');
                newItem.className = 'bulk-item-entry border rounded-lg p-4 bg-gray-50 dark:bg-gray-700 mb-4';
                newItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item</label>
                            <select name="bulk_items[${bulkItemIndex}][included_item_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select Item</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fixed Quantity</label>
                            <input type="number" name="bulk_items[${bulkItemIndex}][fixed_quantity]" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm remove-bulk-item">Remove Item</button>
                `;
                container.appendChild(newItem);
                bulkItemIndex++;
                
                // Show remove button for first item if we have more than one
                const removeButtons = container.querySelectorAll('.remove-bulk-item');
                removeButtons.forEach(btn => btn.style.display = 'inline-block');
            });

            // Remove package item functionality
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-package-item')) {
                    const container = document.getElementById('package_items_container');
                    e.target.closest('.package-item-entry').remove();
                    
                    // Hide remove button for first item if only one remains
                    const removeButtons = container.querySelectorAll('.remove-package-item');
                    if (removeButtons.length === 1) {
                        removeButtons[0].style.display = 'none';
                    }
                }
                
                if (e.target.classList.contains('remove-bulk-item')) {
                    const container = document.getElementById('bulk_items_container');
                    e.target.closest('.bulk-item-entry').remove();
                    
                    // Hide remove button for first item if only one remains
                    const removeButtons = container.querySelectorAll('.remove-bulk-item');
                    if (removeButtons.length === 1) {
                        removeButtons[0].style.display = 'none';
                    }
                }
            });

            // Function to update items details preview
            function updateItemsDetailsPreview() {
                const selectedType = typeSelect.value;
                const itemsDetailsContainer = document.getElementById('items_details_container');
                
                if (!itemsDetailsContainer) return;
                
                itemsDetailsContainer.innerHTML = '';
                
                if (selectedType === 'package') {
                    updatePackageItemsPreview();
                } else if (selectedType === 'bulk') {
                    updateBulkItemsPreview();
                }
            }

            function updatePackageItemsPreview() {
                const itemsDetailsContainer = document.getElementById('items_details_container');
                const packageItems = document.querySelectorAll('select[name*="[included_item_id]"]');
                const maxQuantities = document.querySelectorAll('input[name*="[max_quantity]"]');
                
                itemsDetailsContainer.innerHTML = '<h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Package Items:</h4>';
                
                packageItems.forEach((select, index) => {
                    if (select.value) {
                        const selectedOption = select.options[select.selectedIndex];
                        const itemName = selectedOption.textContent;
                        const maxQuantity = maxQuantities[index] ? maxQuantities[index].value : '1';
                        
                        const itemDetail = document.createElement('div');
                        itemDetail.className = 'border rounded-lg p-3 bg-blue-50 dark:bg-blue-900/20';
                        itemDetail.innerHTML = `
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700 dark:text-gray-300">${itemName}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Max Quantity: ${maxQuantity}</span>
                            </div>
                        `;
                        itemsDetailsContainer.appendChild(itemDetail);
                    }
                });
                
                if (itemsDetailsContainer.children.length === 1) {
                    itemsDetailsContainer.innerHTML += '<p class="text-sm text-gray-500 italic">No items selected yet</p>';
                }
            }

            function updateBulkItemsPreview() {
                const itemsDetailsContainer = document.getElementById('items_details_container');
                const bulkItems = document.querySelectorAll('select[name*="[included_item_id]"]');
                const fixedQuantities = document.querySelectorAll('input[name*="[fixed_quantity]"]');
                
                itemsDetailsContainer.innerHTML = '<h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Bulk Items:</h4>';
                
                bulkItems.forEach((select, index) => {
                    if (select.value) {
                        const selectedOption = select.options[select.selectedIndex];
                        const itemName = selectedOption.textContent;
                        const fixedQuantity = fixedQuantities[index] ? fixedQuantities[index].value : '1';
                        
                        const itemDetail = document.createElement('div');
                        itemDetail.className = 'border rounded-lg p-3 bg-green-50 dark:bg-green-900/20';
                        itemDetail.innerHTML = `
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700 dark:text-gray-300">${itemName}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Fixed Quantity: ${fixedQuantity}</span>
                            </div>
                        `;
                        itemsDetailsContainer.appendChild(itemDetail);
                    }
                });
                
                if (itemsDetailsContainer.children.length === 1) {
                    itemsDetailsContainer.innerHTML += '<p class="text-sm text-gray-500 italic">No items selected yet</p>';
                }
            }

            // Add event listeners for package and bulk item changes
            document.addEventListener('change', function(e) {
                if (e.target.name && (e.target.name.includes('[included_item_id]') || e.target.name.includes('[max_quantity]') || e.target.name.includes('[fixed_quantity]'))) {
                    updateItemsDetailsPreview();
                }
            });

            // Add event listener for type change
            typeSelect.addEventListener('change', togglePackageAndBulkSections);

            // Always trigger initial data load for the selected business
            // This ensures the data matches the selected business
            if (businessSelect && businessSelect.value) {
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    updateFilteredData();
                }, 100);
            }
        });
    </script>
</x-app-layout>

