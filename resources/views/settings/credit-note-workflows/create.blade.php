<x-app-layout>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('credit-note-workflows.index') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                    </svg>
                                    <span class="sr-only">Credit Note Workflows</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500">Create</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create Credit Note Workflow
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Configure the 3-step approval workflow: Supervisor per Service Point (Verifies) → Finance (Authorizes) → CEO (Approves)
                </p>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('credit-note-workflows.store') }}" method="POST" class="px-4 py-5 sm:p-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Business Selection -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700">
                                Business <span class="text-red-500">*</span>
                            </label>
                            <select name="business_id" id="business_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('business_id') border-red-300 @enderror">
                                <option value="">Select a business...</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('business_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Technical Supervisor -->
                        <div>
                            <label for="default_supervisor_user_id" class="block text-sm font-medium text-gray-700">
                                Default Technical Supervisor (Step 1: Verifies)
                            </label>
                            <select name="default_supervisor_user_id" id="default_supervisor_user_id" disabled
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-100 @error('default_supervisor_user_id') border-red-300 @enderror">
                                <option value="">Please select a business first...</option>
                            </select>
                            @error('default_supervisor_user_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">Default supervisor for all service points. Can be overridden per service point below.</p>
                        </div>

                        <!-- Service Points Section -->
                        <div id="service-points-section" class="hidden">
                            <div class="border-t border-gray-200 pt-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">
                                                Service Point Supervisor Permissions
                                            </h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>Supervisors assigned to service points can <strong>reassign "in progress" and "partially done" items</strong> from one user to another. This helps manage workload distribution and handle reassignments when needed.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Service Point Supervisors</h3>
                                <p class="text-sm text-gray-500 mb-4">Assign specific supervisors to each service point. Leave empty to use the default supervisor. These supervisors can reassign in-progress items to other users.</p>
                                <div id="service-points-container" class="space-y-4">
                                    <!-- Service points will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>

                        <!-- Finance (Authorizes Refund) -->
                        <div>
                            <label for="finance_user_id" class="block text-sm font-medium text-gray-700">
                                Finance (Authorizes Refund)
                            </label>
                            <select name="finance_user_id" id="finance_user_id" disabled
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-100 @error('finance_user_id') border-red-300 @enderror">
                                <option value="">Please select a business first...</option>
                            </select>
                            @error('finance_user_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">Step 2: Authorizes the refund</p>
                        </div>

                        <!-- CEO (Approves Refund) -->
                        <div>
                            <label for="ceo_user_id" class="block text-sm font-medium text-gray-700">
                                CEO (Approves Refund)
                            </label>
                            <select name="ceo_user_id" id="ceo_user_id" disabled
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-100 @error('ceo_user_id') border-red-300 @enderror">
                                <option value="">Please select a business first...</option>
                            </select>
                            @error('ceo_user_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">Step 3: Final approval for the refund</p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('credit-note-workflows.index') }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Workflow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const businessSelect = document.getElementById('business_id');
    const defaultSupervisorSelect = document.getElementById('default_supervisor_user_id');
    const financeSelect = document.getElementById('finance_user_id');
    const ceoSelect = document.getElementById('ceo_user_id');
    const servicePointsSection = document.getElementById('service-points-section');
    const servicePointsContainer = document.getElementById('service-points-container');
    
    // All users data with business_id
    const allUsers = @json($allUsers);
    
    // All service points data with business_id
    const allServicePoints = @json($allServicePoints);
    
    // Function to populate user dropdown
    function populateUserDropdown(selectElement, selectedValue = '') {
        const businessId = parseInt(businessSelect.value);
        
        if (!businessId) {
            // No business selected - disable and clear
            selectElement.disabled = true;
            selectElement.classList.add('bg-gray-100');
            selectElement.innerHTML = '<option value="">Please select a business first...</option>';
            return;
        }
        
        // Filter users by selected business
        const businessUsers = allUsers.filter(user => user.business_id === businessId);
        
        // Enable the dropdown
        selectElement.disabled = false;
        selectElement.classList.remove('bg-gray-100');
        
        // Clear and populate options
        selectElement.innerHTML = '<option value="">Select user...</option>';
        
        if (businessUsers.length === 0) {
            selectElement.innerHTML = '<option value="">No users available for this business</option>';
            return;
        }
        
        businessUsers.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.name} (${user.email})`;
            if (selectedValue && user.id == selectedValue) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    }
    
    // Function to load service points for selected business
    function loadServicePoints(businessId) {
        if (!businessId) {
            servicePointsSection.classList.add('hidden');
            return;
        }
        
        // Filter service points by selected business
        const businessServicePoints = allServicePoints.filter(sp => sp.business_id === parseInt(businessId));
        
        if (businessServicePoints.length === 0) {
            servicePointsContainer.innerHTML = '<p class="text-sm text-gray-500">No service points found for this business.</p>';
            servicePointsSection.classList.remove('hidden');
            return;
        }
        
        // Clear container
        servicePointsContainer.innerHTML = '';
        
        // Filter users by business
        const businessUsers = allUsers.filter(user => user.business_id === parseInt(businessId));
        
        // Create service point rows
        businessServicePoints.forEach(servicePoint => {
            const row = document.createElement('div');
            row.className = 'border border-gray-200 rounded-md p-4';
            row.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ${servicePoint.name || 'Unnamed Service Point'}
                            ${servicePoint.description ? `<span class="text-gray-500 text-xs">(${servicePoint.description})</span>` : ''}
                        </label>
                        <input type="hidden" name="service_point_supervisors[${servicePoint.id}][service_point_id]" value="${servicePoint.id}">
                        <select name="service_point_supervisors[${servicePoint.id}][supervisor_user_id]" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Use Default Supervisor</option>
                            ${businessUsers.map(user => `<option value="${user.id}">${user.name} (${user.email})</option>`).join('')}
                        </select>
                    </div>
                </div>
            `;
            servicePointsContainer.appendChild(row);
        });
        
        servicePointsSection.classList.remove('hidden');
    }
    
    // Handle business selection change
    businessSelect.addEventListener('change', function() {
        const businessId = parseInt(this.value);
        const oldDefaultSupervisorValue = defaultSupervisorSelect.value;
        const oldFinanceValue = financeSelect.value;
        const oldCeoValue = ceoSelect.value;
        
        populateUserDropdown(defaultSupervisorSelect, oldDefaultSupervisorValue);
        populateUserDropdown(financeSelect, oldFinanceValue);
        populateUserDropdown(ceoSelect, oldCeoValue);
        loadServicePoints(businessId);
    });
    
    // Initialize - if business is pre-selected (from old input), populate dropdowns
    if (businessSelect.value) {
        businessSelect.dispatchEvent(new Event('change'));
    }
});
</script>
</x-app-layout>

