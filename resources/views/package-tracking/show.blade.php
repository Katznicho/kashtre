<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Package Tracking Details
                        </h2>
                        <div class="flex space-x-3">
                            <a href="{{ route('package-tracking.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                                Back to List
                            </a>
                            <a href="{{ route('package-tracking.edit', $packageTracking) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Client</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->client->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->invoice->invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Package Tracking Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono text-blue-600">{{ $packageTracking->tracking_number ?? 'PKG-' . $packageTracking->id . '-' . $packageTracking->created_at->format('YmdHis') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Package Item</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->packageItem->display_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Included Item</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->includedItem->display_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $packageTracking->status === 'active' ? 'bg-green-100 text-green-800' : ($packageTracking->status === 'expired' ? 'bg-red-100 text-red-800' : ($packageTracking->status === 'fully_used' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $packageTracking->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Quantity Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quantity Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->total_quantity }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Used Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->used_quantity }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Remaining Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->remaining_quantity }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usage Percentage</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($packageTracking->usage_percentage, 1) }}%</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Package Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">UGX {{ number_format($packageTracking->package_price) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Item Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">UGX {{ number_format($packageTracking->item_price) }}</dd>
                            </div>
                        </dl>

                        <!-- Progress Bar -->
                        <div class="mt-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Usage Progress</span>
                                <span>{{ number_format($packageTracking->usage_percentage, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $packageTracking->usage_percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validity Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Validity Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid From</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->valid_from->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->valid_until->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Days Until Expiry</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $packageTracking->days_until_expiry }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Is Expired</dt>
                                <dd class="mt-1">
                                    @if($packageTracking->is_expired)
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Yes</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">No</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Package Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Contents</h3>
                        @if($packageItems->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Point</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($packageItems as $packageItem)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $packageItem->includedItem->display_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $packageItem->includedItem->description ?? 'No description' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $packageItem->includedItem->type === 'service' ? 'bg-blue-100 text-blue-800' : ($packageItem->includedItem->type === 'goods' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($packageItem->includedItem->type ?? 'Unknown') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($packageItem->fixed_quantity)
                                                            {{ $packageItem->fixed_quantity }}
                                                        @elseif($packageItem->max_quantity)
                                                            Up to {{ $packageItem->max_quantity }}
                                                        @else
                                                            Variable
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">UGX {{ number_format($packageItem->includedItem->price ?? 0) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($packageItem->includedItem->servicePoint)
                                                            {{ $packageItem->includedItem->servicePoint->name }}
                                                        @else
                                                            <span class="text-gray-500">Not assigned</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No items found in this package.</p>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                        @if($packageTracking->notes)
                            <p class="text-sm text-gray-900">{{ $packageTracking->notes }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">No notes available.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Use Package Section -->
            @if($packageTracking->status === 'active' && $packageTracking->remaining_quantity > 0 && !$packageTracking->is_expired)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Use Package</h3>
                        <form id="usePackageForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity to Use</label>
                                <input type="number" name="quantity" id="quantity" min="1" max="{{ $packageTracking->remaining_quantity }}" 
                                       value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                    Use Package
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('usePackageForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const quantity = document.getElementById('quantity').value;
            const formData = new FormData();
            formData.append('quantity', quantity);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("package-tracking.use-quantity", $packageTracking) }}', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while using the package.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
</x-app-layout>
