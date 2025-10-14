<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Withdrawal Request') }}
            </h2>
            <a href="{{ route('business-balance-statement.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Balance Statement
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    
                    <!-- Business Info -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Business Information</h3>
                        <p class="text-blue-700"><strong>{{ $business->name }}</strong></p>
                        <p class="text-blue-600">Account: {{ $business->account_number }}</p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('withdrawal-requests.store') }}" method="POST" id="withdrawalForm">
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
                                <input type="number" name="amount" id="amount" required min="0.01" step="0.01"
                                       placeholder="Enter withdrawal amount"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500" id="minAmountText"></p>
                            </div>


                        </div>

                        <!-- Current Balance Display -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg">
                            <h4 class="text-lg font-medium text-green-900 mb-2">Current Account Balance</h4>
                            <p class="text-2xl font-bold text-green-700">{{ number_format($currentBalance, 2) }} UGX</p>
                        </div>

                        <!-- Withdrawal Charge Preview -->
                        <div id="chargePreview" class="mt-6 p-4 bg-yellow-50 rounded-lg hidden">
                            <h4 class="text-lg font-medium text-yellow-900 mb-2">Withdrawal Charge Preview</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Requested Amount:</p>
                                    <p class="text-lg font-semibold" id="requestedAmount">0.00 UGX</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Withdrawal Charge:</p>
                                    <p class="text-lg font-semibold text-red-600" id="withdrawalCharge">0.00 UGX</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Deduction:</p>
                                    <p class="text-lg font-semibold text-orange-600" id="totalDeduction">0.00 UGX</p>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> Your account balance must be at least <span id="requiredBalance" class="font-semibold">0.00 UGX</span> to process this withdrawal.
                                </p>
                                <p class="text-sm text-blue-800 mt-1">
                                    <strong>Current Balance:</strong> <span class="font-semibold">{{ number_format($currentBalance, 2) }} UGX</span>
                                </p>
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

        // Handle amount change
        amountInput.addEventListener('input', calculateCharge);

        function calculateCharge() {
            const amount = parseFloat(amountInput.value) || 0;
            
            if (amount > 0) {
                // Find applicable charge
                const applicableCharge = withdrawalCharges.find(charge => 
                    amount >= charge.lower_bound && amount <= charge.upper_bound
                );
                
                let charge = 0;
                if (applicableCharge) {
                    if (applicableCharge.charge_type === 'fixed') {
                        charge = applicableCharge.charge_amount;
                    } else {
                        charge = (amount * applicableCharge.charge_amount) / 100;
                    }
                }
                
                const totalDeduction = parseFloat(amount) + parseFloat(charge);
                
                // Update preview
                document.getElementById('requestedAmount').textContent = amount.toLocaleString() + ' UGX';
                document.getElementById('withdrawalCharge').textContent = charge.toLocaleString() + ' UGX';
                document.getElementById('totalDeduction').textContent = totalDeduction.toLocaleString() + ' UGX';
                document.getElementById('requiredBalance').textContent = totalDeduction.toLocaleString() + ' UGX';
                
                chargePreview.classList.remove('hidden');
            } else {
                chargePreview.classList.add('hidden');
            }
        }
    </script>

</x-app-layout>
