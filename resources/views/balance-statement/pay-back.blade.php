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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Pending Payment Entries</h3>
                        <div class="flex space-x-2">
                            <button type="button" id="selectAllBtn" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Select All
                            </button>
                            <button type="button" id="deselectAllBtn" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    @if($ppEntries->count() > 0)
                        <form id="payBackForm" action="{{ route('balance-statement.pay-back', $client->id) }}" method="POST">
                            @csrf
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAll" title="Select All">
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($ppEntries as $index => $entry)
                                            <tr class="{{ $entry['is_service_charge'] ? 'bg-yellow-50' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <input type="checkbox" 
                                                           name="entry_ids[]" 
                                                           value="{{ $entry['id'] }}"
                                                           class="entry-checkbox"
                                                           data-amount="{{ $entry['amount'] }}">
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
                                    @if(!empty($availablePaymentMethods))
                                        @foreach($availablePaymentMethods as $method)
                                            <option value="{{ $method }}">
                                                {{ $paymentMethodNames[$method] ?? ucwords(str_replace('_', ' ', $method)) }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No payment methods configured for this business</option>
                                    @endif
                                </select>
                                @if(empty($availablePaymentMethods))
                                    <p class="mt-2 text-sm text-red-600">No payment methods have been set up for this business. Please contact the administrator.</p>
                                @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateTotal() {
                var checkboxes = document.querySelectorAll('.entry-checkbox:checked');
                var total = 0;
                
                for (var i = 0; i < checkboxes.length; i++) {
                    var amount = parseFloat(checkboxes[i].getAttribute('data-amount')) || 0;
                    total += amount;
                }
                
                var totalElement = document.getElementById('selectedTotal');
                if (totalElement) {
                    totalElement.textContent = 'UGX ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }
                
                var totalAmountInput = document.getElementById('total_amount');
                if (totalAmountInput) {
                    totalAmountInput.value = total;
                }
                
                var submitBtn = document.getElementById('submitBtn');
                var paymentMethod = document.getElementById('payment_method');
                
                if (submitBtn) {
                    if (checkboxes.length > 0 && paymentMethod && paymentMethod.value && total > 0) {
                        submitBtn.disabled = false;
                    } else {
                        submitBtn.disabled = true;
                    }
                }
            }
            
            function updateSelectAllState() {
                var allCheckboxes = document.querySelectorAll('.entry-checkbox');
                var checkedCheckboxes = document.querySelectorAll('.entry-checkbox:checked');
                var selectAllCheckbox = document.getElementById('selectAll');
                
                if (selectAllCheckbox && allCheckboxes.length > 0) {
                    if (checkedCheckboxes.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCheckboxes.length === allCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }
            
            // Attach listeners to all checkboxes
            var entryCheckboxes = document.querySelectorAll('.entry-checkbox');
            for (var i = 0; i < entryCheckboxes.length; i++) {
                entryCheckboxes[i].addEventListener('change', function() {
                    updateTotal();
                    updateSelectAllState();
                });
            }
            
            // Select All checkbox
            var selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    var isChecked = this.checked;
                    var entryCheckboxes = document.querySelectorAll('.entry-checkbox');
                    for (var i = 0; i < entryCheckboxes.length; i++) {
                        entryCheckboxes[i].checked = isChecked;
                    }
                    updateTotal();
                    updateSelectAllState();
                });
            }
            
            // Select All button
            var selectAllBtn = document.getElementById('selectAllBtn');
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    var entryCheckboxes = document.querySelectorAll('.entry-checkbox');
                    for (var i = 0; i < entryCheckboxes.length; i++) {
                        entryCheckboxes[i].checked = true;
                    }
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    }
                    updateTotal();
                    updateSelectAllState();
                });
            }
            
            // Deselect All button
            var deselectAllBtn = document.getElementById('deselectAllBtn');
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    var entryCheckboxes = document.querySelectorAll('.entry-checkbox');
                    for (var i = 0; i < entryCheckboxes.length; i++) {
                        entryCheckboxes[i].checked = false;
                    }
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                    updateTotal();
                    updateSelectAllState();
                });
            }
            
            // Payment method change
            var paymentMethod = document.getElementById('payment_method');
            if (paymentMethod) {
                paymentMethod.addEventListener('change', updateTotal);
            }
            
            // Form submission
            var payBackForm = document.getElementById('payBackForm');
            if (payBackForm) {
                payBackForm.addEventListener('submit', function(e) {
                    var checkboxes = document.querySelectorAll('.entry-checkbox:checked');
                    if (checkboxes.length === 0) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'No Items Selected',
                                text: 'Please select at least one item to pay.',
                            });
                        } else {
                            alert('Please select at least one item to pay.');
                        }
                        return false;
                    }

                    var total = parseFloat(document.getElementById('total_amount').value);
                    if (total <= 0) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Amount',
                                text: 'Total amount must be greater than zero.',
                            });
                        } else {
                            alert('Total amount must be greater than zero.');
                        }
                        return false;
                    }

                    var paymentMethodValue = document.getElementById('payment_method').value;
                    
                    if (paymentMethodValue === 'mobile_money') {
                        e.preventDefault();
                        showPaymentSummary();
                    } else {
                        e.preventDefault();
                        var totalFormatted = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Confirm Payment',
                                html: 'Are you sure you want to process payment of <strong>UGX ' + totalFormatted + '</strong>?<br><br>This will mark the selected items as paid and create a payment invoice without service charge.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#10b981',
                                cancelButtonColor: '#6b7280',
                                confirmButtonText: 'Yes, Process Payment',
                                cancelButtonText: 'Cancel'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        title: 'Processing Payment',
                                        html: 'Please wait while we process your payment...',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: function() {
                                            Swal.showLoading();
                                        }
                                    });
                                    payBackForm.submit();
                                }
                            });
                        } else {
                            if (confirm('Are you sure you want to process payment of UGX ' + totalFormatted + '?')) {
                                payBackForm.submit();
                            }
                        }
                    }
                });
            }
            
            // Mobile money payment summary
            window.showPaymentSummary = function() {
                var checkboxes = document.querySelectorAll('.entry-checkbox:checked');
                var entryIds = [];
                for (var i = 0; i < checkboxes.length; i++) {
                    entryIds.push(checkboxes[i].value);
                }
                var paymentMethodValue = document.getElementById('payment_method').value;
                var totalAmount = parseFloat(document.getElementById('total_amount').value);
                
                if (entryIds.length === 0 || totalAmount <= 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Selection',
                            text: 'Please select at least one item to pay.',
                        });
                    }
                    return;
                }
                
                Swal.fire({
                    title: 'Loading Payment Summary',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                
                fetch('{{ route("balance-statement.pay-back.summary", $client->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        entry_ids: entryIds,
                        payment_method: paymentMethodValue,
                        total_amount: totalAmount
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        var summary = data.summary;
                        var itemsHtml = '<div class="text-left max-h-60 overflow-y-auto mb-4">';
                        itemsHtml += '<table class="min-w-full text-sm">';
                        itemsHtml += '<thead><tr class="border-b"><th class="text-left py-2">Item</th><th class="text-right py-2">Amount</th></tr></thead>';
                        itemsHtml += '<tbody>';
                        for (var i = 0; i < summary.items.length; i++) {
                            var item = summary.items[i];
                            var itemAmount = parseFloat(item.total_amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            itemsHtml += '<tr class="border-b"><td class="py-2">' + item.name + '</td><td class="text-right py-2">UGX ' + itemAmount + '</td></tr>';
                        }
                        itemsHtml += '</tbody>';
                        var totalFormatted = parseFloat(summary.total_amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        itemsHtml += '<tfoot><tr class="font-bold border-t-2"><td class="py-2">Total</td><td class="text-right py-2">UGX ' + totalFormatted + '</td></tr></tfoot>';
                        itemsHtml += '</table></div>';
                        
                        Swal.fire({
                            title: 'Payment Summary',
                            html: '<div class="text-left"><p class="mb-2"><strong>Invoice Number:</strong> ' + summary.invoice_number + '</p><p class="mb-2"><strong>Client:</strong> ' + summary.client_name + '</p><p class="mb-4"><strong>Phone:</strong> ' + summary.client_phone + '</p>' + itemsHtml + '<div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-2">Payment Phone Number</label><input type="text" id="payment_phone_input" value="' + summary.payment_phone + '" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Enter phone number"><p class="text-xs text-gray-500 mt-1">You can update the phone number if different from client phone</p></div></div>',
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Initiate Payment',
                            cancelButtonText: 'Cancel',
                            didOpen: function() {
                                var phoneInput = document.getElementById('payment_phone_input');
                                if (phoneInput) {
                                    phoneInput.focus();
                                    phoneInput.select();
                                }
                            },
                            preConfirm: function() {
                                var phoneInput = document.getElementById('payment_phone_input');
                                var phone = phoneInput ? phoneInput.value.trim() : summary.payment_phone;
                                if (!phone) {
                                    Swal.showValidationMessage('Please enter a phone number');
                                    return false;
                                }
                                return phone;
                            }
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                initiateMobileMoneyPayment(entryIds, paymentMethodValue, totalAmount, result.value);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to load payment summary.',
                        });
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load payment summary. Please try again.',
                    });
                });
            };
            
            // Initiate mobile money payment
            window.initiateMobileMoneyPayment = function(entryIds, paymentMethod, totalAmount, paymentPhone) {
                Swal.fire({
                    title: 'Initiating Payment',
                    html: 'Please wait while we initiate the payment...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                
                var formData = new FormData();
                for (var i = 0; i < entryIds.length; i++) {
                    formData.append('entry_ids[]', entryIds[i]);
                }
                formData.append('payment_method', paymentMethod);
                formData.append('total_amount', totalAmount);
                formData.append('payment_phone', paymentPhone);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                fetch('{{ route("balance-statement.pay-back", $client->id) }}', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Initiated',
                            html: '<p>' + data.message + '</p><p class="text-sm mt-2">Invoice Number: <strong>' + data.invoice_number + '</strong></p>',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = '{{ route("balance-statement.show", $client->id) }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: data.message || 'Failed to initiate payment. Please try again.',
                        });
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to initiate payment. Please try again.',
                    });
                });
            };
            
            // Initialize
            updateTotal();
            updateSelectAllState();
        });
    </script>
</x-app-layout>
