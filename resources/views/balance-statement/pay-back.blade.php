<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pay Back Outstanding Amount') }} - {{ $client->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Client Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $client->name }}</h3>
                            <p class="text-sm text-gray-500">Client ID: {{ $client->client_id }}</p>
                            <p class="text-sm text-gray-500">Phone: {{ $client->phone_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Outstanding</p>
                            <p class="text-2xl font-bold text-red-600">
                                UGX {{ number_format($totalOutstanding, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PP Entries List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pending Payment Entries</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Service charges are listed first, followed by other items from oldest to newest.
                        An invoice generated using this method doesn't attract a service charge.
                    </p>

                    @if($ppEntries->count() > 0)
                        <form id="payBackForm" action="{{ route('balance-statement.pay-back', $client->id) }}" method="POST">
                            @csrf
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAll" onchange="toggleAllEntries(this)">
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($ppEntries as $entry)
                                            <tr class="{{ $entry['is_service_charge'] ? 'bg-yellow-50' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <input type="checkbox" 
                                                           name="entry_ids[]" 
                                                           value="{{ $entry['id'] }}"
                                                           class="entry-checkbox"
                                                           data-amount="{{ $entry['amount'] }}"
                                                           onchange="updateTotal()">
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry['date'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    {{ $entry['description'] }}
                                                    @if($entry['is_service_charge'])
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            Service Charge
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry['invoice_number'] ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                    UGX {{ number_format($entry['amount'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                Selected Total:
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900" id="selectedTotal">
                                                UGX 0.00
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>

                            <!-- Hidden field for total amount -->
                            <input type="hidden" name="total_amount" id="total_amount" value="0">

                            <!-- Submit Button -->
                            <div class="mt-6 flex justify-end space-x-4">
                                <a href="{{ route('balance-statement.show', $client->id) }}" 
                                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        id="submitBtn"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    Process Payment
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No pending payment entries found for this client.</p>
                            <a href="{{ route('balance-statement.show', $client->id) }}" 
                               class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                Back to Balance Statement
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleAllEntries(checkbox) {
            const checkboxes = document.querySelectorAll('.entry-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateTotal();
        }

        function updateTotal() {
            const checkboxes = document.querySelectorAll('.entry-checkbox:checked');
            let total = 0;
            checkboxes.forEach(cb => {
                total += parseFloat(cb.dataset.amount);
            });
            
            document.getElementById('selectedTotal').textContent = 'UGX ' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            document.getElementById('total_amount').value = total;
            
            // Enable/disable submit button
            const submitBtn = document.getElementById('submitBtn');
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (checkboxes.length > 0 && paymentMethod && total > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Update total when payment method changes
        document.getElementById('payment_method').addEventListener('change', updateTotal);

        // Handle form submission
        document.getElementById('payBackForm').addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('.entry-checkbox:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'No Items Selected',
                    text: 'Please select at least one item to pay.',
                });
                return false;
            }

            const total = parseFloat(document.getElementById('total_amount').value);
            if (total <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Total amount must be greater than zero.',
                });
                return false;
            }

            // Show confirmation
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Payment',
                html: `Are you sure you want to process payment of <strong>UGX ${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>?<br><br>This will mark the selected items as paid and create a payment invoice without service charge.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Process Payment',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing Payment',
                        html: 'Please wait while we process your payment...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form
                    this.submit();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

