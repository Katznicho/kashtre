<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Contractor Withdrawal Request') }}
            </h2>
            <a href="{{ route('contractor-balance-statement.show', $contractorProfile) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Contractor Account Statement
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    
                    <!-- Contractor Info -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Contractor Information</h3>
                        <p class="text-blue-700"><strong>{{ $contractorProfile->user->name ?? 'Contractor' }}</strong></p>
                        <p class="text-blue-600">Business: {{ $business->name }}</p>
                        <p class="text-blue-600">Current Balance: UGX {{ number_format($currentBalance, 2) }}</p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('contractor-withdrawal-requests.store', $contractorProfile) }}" method="POST" id="withdrawalForm">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Withdrawal Type -->
                            <div class="md:col-span-2">
                                <label for="withdrawal_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Withdrawal Type <span class="text-red-500">*</span>
                                </label>
                                <select name="withdrawal_type" id="withdrawal_type" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select withdrawal type</option>
                                    @foreach($withdrawalSettings as $setting)
                                        <option value="{{ $setting->withdrawal_type }}" 
                                                data-min-amount="{{ $setting->minimum_withdrawal_amount }}">
                                            {{ ucfirst($setting->withdrawal_type) }} 
                                            (Min: {{ number_format($setting->minimum_withdrawal_amount, 2) }} UGX)
                                        </option>
                                    @endforeach
                                </select>
                                @error('withdrawal_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Amount (UGX) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       placeholder="Enter amount e.g. 1400.00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p id="minAmountText" class="mt-1 text-sm text-gray-500"></p>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            

                            <!-- Reason -->
                            <div class="md:col-span-2">
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason for Withdrawal
                                </label>
                                <textarea name="reason" 
                                          id="reason" 
                                          rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Optional: Explain the reason for this withdrawal request"></textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Charge Preview -->
                        <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                            <h4 class="text-lg font-medium text-yellow-900 mb-2">Withdrawal Summary</h4>
                            <div id="chargePreview" class="text-sm text-yellow-800">
                                <p>Withdrawal amount: <span id="previewAmount">0.00</span> UGX</p>
                                <p>Withdrawal charge: <span id="previewCharge">0.00</span> UGX</p>
                                <p class="font-semibold">Net amount to contractor: <span id="previewNet">0.00</span> UGX</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                Create Withdrawal Request
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for dynamic form behavior -->
    <script>
        const withdrawalCharges = @json($withdrawalCharges);
        const amountInput = document.getElementById('amount');
        const withdrawalTypeSelect = document.getElementById('withdrawal_type');
        const chargePreview = document.getElementById('chargePreview');
        const minAmountText = document.getElementById('minAmountText');
        // Removed payment method specific sections

        // Handle withdrawal type change
        withdrawalTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const minAmount = selectedOption.getAttribute('data-min-amount');
            
            if (minAmount) {
                minAmountText.textContent = `Minimum amount: ${parseFloat(minAmount).toLocaleString()} UGX`;
                amountInput.min = minAmount;
            } else {
                minAmountText.textContent = '';
                amountInput.min = 0.01;
            }
            
            calculateCharge();
        });

        // No-op: details sections removed

        // Handle amount change
        amountInput.addEventListener('input', calculateCharge);

        function calculateCharge() {
            const amount = parseFloat(amountInput.value) || 0;
            const withdrawalType = withdrawalTypeSelect.value;
            
            let charge = 0;
            
            if (withdrawalType && withdrawalCharges.length > 0) {
                const applicableCharge = withdrawalCharges.find(charge => 
                    amount >= charge.lower_bound && amount <= charge.upper_bound
                );
                
                if (applicableCharge) {
                    if (applicableCharge.charge_type === 'percentage') {
                        charge = (amount * applicableCharge.charge_amount) / 100;
                    } else {
                        charge = applicableCharge.charge_amount;
                    }
                }
            }
            
            const netAmount = amount - charge;
            
            document.getElementById('previewAmount').textContent = amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            document.getElementById('previewCharge').textContent = charge.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            document.getElementById('previewNet').textContent = netAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Initialize
        calculateCharge();
    </script>
</x-app-layout>
