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

                <!-- Button Labels Configuration -->
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Button Labels</h3>
                    
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
                            Label for the button that discharges patients (removes long-stay status).
                        </p>
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
</x-app-layout>

