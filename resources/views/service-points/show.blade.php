<x-app-layout>
    <style>
        .tab-button {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .tab-button.active {
            border-bottom-color: #3b82f6 !important;
            color: #2563eb !important;
        }
        .tab-button:hover {
            border-bottom-color: #6b7280 !important;
            color: #374151 !important;
        }
        .tab-content {
            display: block;
        }
        .tab-content.hidden {
            display: none;
        }
    </style>
    
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('service-queues.index') }}" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-3xl font-bold text-[#011478]">{{ $servicePoint->name }}</h2>
                        <p class="text-gray-600 mt-2">{{ $servicePoint->branch->name ?? 'N/A' }} - Service Point Management</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Total Patients Today</div>
                        <div class="text-2xl font-bold text-[#011478]">
                            {{ $servicePoint->pendingDeliveryQueues->count() + $servicePoint->partiallyDoneDeliveryQueues->count() + $servicePoint->serviceDeliveryQueues->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Point Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Service Point Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $servicePoint->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $servicePoint->branch->name ?? 'N/A' }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Queue Statistics -->
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $servicePoint->pendingDeliveryQueues->count() }}</div>
                            <div class="text-sm text-gray-600">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $servicePoint->partiallyDoneDeliveryQueues->count() }}</div>
                            <div class="text-sm text-gray-600">Partially Done</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $servicePoint->serviceDeliveryQueues->count() }}</div>
                            <div class="text-sm text-gray-600">Completed Today</div>
                        </div>
                    </div>

                    <!-- Service Point Queues - Tab Based -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Patient Service Queue</h4>
                        
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button onclick="switchTab('pending')" 
                                        id="tab-pending"
                                        class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm active">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                                        Pending ({{ $servicePoint->pendingDeliveryQueues->count() }})
                                    </span>
                                </button>
                                <button onclick="switchTab('in-progress')" 
                                        id="tab-in-progress"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                                        Partially Done ({{ $servicePoint->partiallyDoneDeliveryQueues->count() }})
                                    </span>
                                </button>
                                <button onclick="switchTab('completed')" 
                                        id="tab-completed"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                        Completed ({{ $servicePoint->serviceDeliveryQueues->count() }})
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div id="tab-content">
                            <!-- Pending Tab Content -->
                            <div id="content-pending" class="tab-content">
                                @if($servicePoint->pendingDeliveryQueues->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Patient</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Service</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Price</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Qty</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Invoice</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Time</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($servicePoint->pendingDeliveryQueues as $item)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900">
                                                            {{ $item->client->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $item->item_name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 font-semibold">
                                                            {{ number_format($item->price, 0) }} UGX
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                                        <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                                        <td class="px-4 py-3 text-gray-600">{{ $item->queued_at ? $item->queued_at->format('H:i') : 'N/A' }}</td>
                                                        <td class="px-4 py-3">
                                                            <button onclick="moveToPartiallyDone({{ $item->id }})" 
                                                                    class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded transition-colors">
                                                                Start
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No pending patients</div>
                                    </div>
                                @endif
                            </div>

                            <!-- In Progress Tab Content -->
                            <div id="content-in-progress" class="tab-content hidden">
                                @if($servicePoint->partiallyDoneDeliveryQueues->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Patient</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Service</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Price</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Qty</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Started</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Attending</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($servicePoint->partiallyDoneDeliveryQueues as $item)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900">
                                                            {{ $item->client->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $item->item_name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 font-semibold">
                                                            {{ number_format($item->price, 0) }} UGX
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                                        <td class="px-4 py-3 text-gray-600">{{ $item->started_at ? $item->started_at->format('H:i') : 'N/A' }}</td>
                                                        <td class="px-4 py-3 text-gray-600">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                {{ $item->startedByUser->name ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <button onclick="moveToCompleted({{ $item->id }})" 
                                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition-colors">
                                                                Complete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No patients currently being served</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Completed Tab Content -->
                            <div id="content-completed" class="tab-content hidden">
                                @if($servicePoint->serviceDeliveryQueues->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Patient</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Service</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Price</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Qty</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Completed</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($servicePoint->serviceDeliveryQueues as $item)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900">
                                                            {{ $item->client->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $item->item_name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 font-semibold">
                                                            {{ number_format($item->price, 0) }} UGX
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                                        <td class="px-4 py-3 text-gray-600">{{ $item->completed_at ? $item->completed_at->format('H:i') : 'N/A' }}</td>
                                                        <td class="px-4 py-3">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                ✓ Done
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No completed services today</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function moveToPartiallyDone(itemId) {
            // Show confirmation dialog with Sweet Alert
            Swal.fire({
                title: 'Move to In Progress?',
                text: 'This will process money transfers and move the item to in progress status.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, move it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`/service-delivery-queues/${itemId}/move-to-partially-done`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'An error occurred');
                        }
                        return data;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Reload the page to show updated status
                        window.location.reload();
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        function moveToCompleted(itemId) {
            // Show confirmation dialog with Sweet Alert
            Swal.fire({
                title: 'Mark as Completed?',
                text: 'This will mark the item as completed and move it to the completed list.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, complete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`/service-delivery-queues/${itemId}/move-to-completed`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'An error occurred');
                        }
                        return data;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Completed!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Reload the page to show updated status
                        window.location.reload();
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('#tab-content .tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Show the selected tab content
            const selectedContent = document.getElementById(`content-${tabName}`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }

            // Add active class to the selected tab button
            const selectedButton = document.getElementById(`tab-${tabName}`);
            if (selectedButton) {
                selectedButton.classList.add('active', 'border-blue-500', 'text-blue-600');
                selectedButton.classList.remove('border-transparent', 'text-gray-500');
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</x-app-layout>
