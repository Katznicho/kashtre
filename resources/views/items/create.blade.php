
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
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" id="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item code" value="{{ old('code') }}">
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
                            <p class="mt-1 text-sm text-gray-500">If less than 100%, a contractor must be selected</p>
                        </div>

                        <!-- Group -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subgroup -->
                        <div>
                            <label for="subgroup_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subgroup</label>
                            <select name="subgroup_id" id="subgroup_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('subgroup_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                            <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- UOM -->
                        <div>
                            <label for="uom_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure</label>
                            <select name="uom_id" id="uom_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($itemUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ old('uom_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Point -->
                        <div>
                            <label for="service_point_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Point</label>
                            <select name="service_point_id" id="service_point_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($servicePoints as $point)
                                    <option value="{{ $point->id }}" {{ old('service_point_id') == $point->id ? 'selected' : '' }}>{{ $point->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Contractor (shown only when hospital share < 100%) -->
                        <div id="contractor_div" style="display: none;">
                            <label for="contractor_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contractor <span class="text-red-500">*</span></label>
                            <select name="contractor_account_id" id="contractor_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select Contractor</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('contractor_account_id') == $contractor->id ? 'selected' : '' }}>
                                        {{ $contractor->account_name }} ({{ $contractor->business->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Other Name -->
                        <div class="md:col-span-2">
                            <label for="other_names" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Name</label>
                            <input type="text" name="other_names" id="other_names" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter other name" value="{{ old('other_names') }}">
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
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use default price</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

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
                    // Make branch price inputs required when custom pricing is selected
                    const branchPriceInputs = branchPricingSection.querySelectorAll('input[name*="[price]"]');
                    branchPriceInputs.forEach(input => {
                        input.required = true;
                    });
                } else {
                    branchPricingSection.style.display = 'none';
                    // Remove required attribute when default pricing is selected
                    const branchPriceInputs = branchPricingSection.querySelectorAll('input[name*="[price]"]');
                    branchPriceInputs.forEach(input => {
                        input.required = false;
                        input.value = ''; // Clear values when hiding
                    });
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
                               placeholder="Enter price"
                               ${customPricing && customPricing.checked ? 'required' : ''}>
                        <p class="mt-1 text-xs text-gray-500">${customPricing && customPricing.checked ? 'Required when custom pricing is selected' : 'Leave empty to use default price'}</p>
                    `;
                    grid.appendChild(branchDiv);
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

