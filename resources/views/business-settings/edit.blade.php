<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Business Settings</h1>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('business-settings.update') }}" method="POST" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                @csrf
                @method('PUT')
                
                <!-- Credit Limits -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Credit Limits</h3>
                    
                    <!-- Maximum Third Party Credit Limit -->
                    <div class="mb-4">
                        <label for="max_third_party_credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Third Party Credit Limit (UGX)
                        </label>
                        <input 
                            type="number" 
                            name="max_third_party_credit_limit" 
                            id="max_third_party_credit_limit" 
                            step="0.01" 
                            min="0"
                            value="{{ old('max_third_party_credit_limit', $business->max_third_party_credit_limit) }}" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="0.00"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Maximum amount of credit the entity can accept from a third party payer (e.g., insurance companies).
                        </p>
                    </div>

                    <!-- Maximum First Party Credit Limit -->
                    <div class="mb-4">
                        <label for="max_first_party_credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum First Party Credit Limit (UGX)
                        </label>
                        <input 
                            type="number" 
                            name="max_first_party_credit_limit" 
                            id="max_first_party_credit_limit" 
                            step="0.01" 
                            min="0"
                            value="{{ old('max_first_party_credit_limit', $business->max_first_party_credit_limit) }}" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="0.00"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Maximum amount of credit the entity can accept from a first party payer (e.g., the client themselves).
                        </p>
                    </div>
                </div>

                <!-- Payment Terms Configuration -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Terms</h3>
                    
                    <!-- Default Payment Terms Days -->
                    <div class="mb-4">
                        <label for="default_payment_terms_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Default Payment Terms (Days)
                        </label>
                        <input 
                            type="number" 
                            name="default_payment_terms_days" 
                            id="default_payment_terms_days" 
                            min="1"
                            max="365"
                            value="{{ old('default_payment_terms_days', $business->default_payment_terms_days ?? 30) }}" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="30"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Default number of days from invoice date until payment is due. This is used when creating accounts receivable entries for credit clients. Default is 30 days.
                        </p>
                    </div>
                </div>

                <!-- Admission & Discharge Configuration -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admission & Discharge Settings</h3>
                    
                    <!-- Admit Button Label -->
                    <div class="mb-4">
                        <label for="admit_button_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Admit Button Label
                        </label>
                        <input 
                            type="text" 
                            name="admit_button_label" 
                            id="admit_button_label" 
                            value="{{ old('admit_button_label', $business->admit_button_label ?? 'Admit Patient') }}" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Admit Patient"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Label for the button that admits patients (enables credit and/or long-stay).
                        </p>
                    </div>

                    <!-- Admission Behavior -->
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">What happens when "Admit" is clicked:</h4>
                        <div class="space-y-3">
                            <label class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    name="admit_enable_credit" 
                                    id="admit_enable_credit" 
                                    value="1"
                                    {{ old('admit_enable_credit', $business->admit_enable_credit) ? 'checked' : '' }}
                                    class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Credit Services</span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Visit ID will have <strong>/C</strong> suffix. Allows client to access services on credit.</p>
                                </div>
                            </label>
                            <label class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    name="admit_enable_long_stay" 
                                    id="admit_enable_long_stay" 
                                    value="1"
                                    {{ old('admit_enable_long_stay', $business->admit_enable_long_stay) ? 'checked' : '' }}
                                    class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Long-Stay / Inpatient</span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Visit ID will have <strong>/M</strong> suffix. Visit ID stays active until manually discharged.</p>
                                </div>
                            </label>
                        </div>
                        <p class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                            <strong>Note:</strong> If both are selected, the visit ID will have <strong>/C/M</strong> suffix. If neither is selected, a modal will appear asking which option(s) to enable.
                        </p>
                    </div>

                    <!-- Discharge Behavior -->
                    <div class="mb-4 p-4 bg-red-50 dark:bg-gray-700 rounded-lg border border-red-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">What happens when "Discharge" is clicked:</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Visit ID resets to default format.</p>
                    </div>

                    <!-- Discharge Button Label -->
                    <div class="mb-4">
                        <label for="discharge_button_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Discharge Button Label
                        </label>
                        <input 
                            type="text" 
                            name="discharge_button_label" 
                            id="discharge_button_label" 
                            value="{{ old('discharge_button_label', $business->discharge_button_label ?? 'Discharge Patient') }}" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Discharge Patient"
                        >
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Label for the button that discharges patients.
                        </p>
                    </div>
                </div>

                <!-- Credit Exclusions -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Credit Service Exclusions</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Select items from your price list that should be excluded from credit terms. Invoices containing excluded items will not be saved for credit clients.
                    </p>
                    
                    <div class="mb-4">
                        <label for="credit_excluded_items" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Excluded Items
                        </label>
                        
                        <!-- Quick Filter Buttons -->
                        <div class="mb-3 flex flex-wrap gap-2">
                            <button type="button" class="filter-btn px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active" data-filter="all">
                                All Items
                            </button>
                            <button type="button" class="filter-btn px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600" data-filter="service">
                                Services
                            </button>
                            <button type="button" class="filter-btn px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600" data-filter="good">
                                Goods
                            </button>
                            <button type="button" class="filter-btn px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600" data-filter="package">
                                Packages
                            </button>
                            <button type="button" class="filter-btn px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600" data-filter="bulk">
                                Bulk Items
                            </button>
                        </div>
                        
                        <select 
                            name="credit_excluded_items[]" 
                            id="credit_excluded_items" 
                            multiple
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        >
                            @foreach($items as $item)
                                <option 
                                    value="{{ $item->id }}"
                                    data-type="{{ $item->type }}"
                                    {{ in_array($item->id, old('credit_excluded_items', $business->credit_excluded_items ?? [])) ? 'selected' : '' }}
                                >
                                    {{ $item->name }}@if($item->code) ({{ $item->code }})@endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <strong>Tip:</strong> Use quick filters to narrow down items by type, then search and select multiple items. Invoices with excluded items cannot be saved for credit clients.
                        </p>
                    </div>
                </div>

                <!-- Credit Limit Approval Workflow -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Credit Limit Approval Workflow</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Select users who will be part of the 3-step approval process for credit limit changes (for both clients and third-party payers).
                    </p>
                    
                    <!-- Level 1: Initiators -->
                    <div class="mb-6 p-4 bg-green-50 dark:bg-gray-700 rounded-lg border border-green-200 dark:border-gray-600">
                        <h4 class="text-md font-medium text-gray-800 dark:text-white mb-3">Level 1: Initiators</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">People who can initiate credit limit change requests.</p>
                        <div class="mb-3">
                            <input type="text" 
                                   id="search-initiators" 
                                   placeholder="Search by name or email..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        <div id="initiators-container" class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($users->where('business_id', $business->id) as $user)
                                <div class="flex items-center space-x-3 initiator-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                    <input type="checkbox" 
                                           name="credit_limit_initiators[]" 
                                           value="user:{{ $user->id }}"
                                           id="initiator_{{ $user->id }}"
                                           {{ $business->creditLimitApprovers->where('approval_level', 'initiator')->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    <label for="initiator_{{ $user->id }}" class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Level 2: Authorizers -->
                    <div class="mb-6 p-4 bg-yellow-50 dark:bg-gray-700 rounded-lg border border-yellow-200 dark:border-gray-600">
                        <h4 class="text-md font-medium text-gray-800 dark:text-white mb-3">Level 2: Authorizers</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">People who authorize credit limit change requests after initiation.</p>
                        <div class="mb-3">
                            <input type="text" 
                                   id="search-authorizers" 
                                   placeholder="Search by name or email..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div id="authorizers-container" class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($users->where('business_id', $business->id) as $user)
                                <div class="flex items-center space-x-3 authorizer-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                    <input type="checkbox" 
                                           name="credit_limit_authorizers[]" 
                                           value="user:{{ $user->id }}"
                                           id="authorizer_{{ $user->id }}"
                                           {{ $business->creditLimitApprovers->where('approval_level', 'authorizer')->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                           class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                    <label for="authorizer_{{ $user->id }}" class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Level 3: Approvers -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                        <h4 class="text-md font-medium text-gray-800 dark:text-white mb-3">Level 3: Approvers</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">People who give final approval for credit limit change requests.</p>
                        <div class="mb-3">
                            <input type="text" 
                                   id="search-approvers" 
                                   placeholder="Search by name or email..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div id="approvers-container" class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($users->where('business_id', $business->id) as $user)
                                <div class="flex items-center space-x-3 approver-item" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                                    <input type="checkbox" 
                                           name="credit_limit_approvers[]" 
                                           value="user:{{ $user->id }}"
                                           id="approver_{{ $user->id }}"
                                           {{ $business->creditLimitApprovers->where('approval_level', 'approver')->where('approver_type', 'user')->where('approver_id', $user->id)->count() > 0 ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="approver_{{ $user->id }}" class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .filter-btn.active {
            background-color: #2563eb !important;
            color: white !important;
            border-color: #2563eb !important;
        }
        .filter-btn.active:hover {
            background-color: #1d4ed8 !important;
        }
    </style>
    
    <!-- Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Initialize Select2 for credit excluded items
        $(document).ready(function() {
            const $select = $('#credit_excluded_items');
            
            $select.select2({
                theme: 'bootstrap-5',
                placeholder: 'Select items to exclude from credit terms',
                allowClear: true,
                width: '100%'
            });
            
            // Quick filter functionality
            $('.filter-btn').on('click', function() {
                const filter = $(this).data('filter');
                
                // Update active button
                $('.filter-btn').removeClass('active bg-blue-600 text-white').addClass('bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300');
                $(this).removeClass('bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300').addClass('active bg-blue-600 text-white');
                
                // Filter options
                if (filter === 'all') {
                    $select.find('option').prop('disabled', false);
                } else {
                    $select.find('option').each(function() {
                        const $option = $(this);
                        if ($option.data('type') === filter) {
                            $option.prop('disabled', false);
                        } else {
                            $option.prop('disabled', true);
                        }
                    });
                }
                
                // Update Select2 to reflect changes
                $select.trigger('change.select2');
                
                // Open Select2 dropdown to show filtered results
                $select.select2('open');
            });
        });

        // Search functionality for approvers
        document.addEventListener('DOMContentLoaded', function() {
            // Initiators search
            const initiatorSearch = document.getElementById('search-initiators');
            if (initiatorSearch) {
                initiatorSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.initiator-item');
                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        const email = item.getAttribute('data-email');
                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Authorizers search
            const authorizerSearch = document.getElementById('search-authorizers');
            if (authorizerSearch) {
                authorizerSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.authorizer-item');
                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        const email = item.getAttribute('data-email');
                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Approvers search
            const approverSearch = document.getElementById('search-approvers');
            if (approverSearch) {
                approverSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.approver-item');
                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        const email = item.getAttribute('data-email');
                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>

