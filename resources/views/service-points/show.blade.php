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
        .client-card {
            transition: all 0.2s ease-in-out;
        }
        .client-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-partially-done {
            background-color: #fed7aa;
            color: #ea580c;
        }
        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

    </style>
    
    @php
        // Ensure $clientsWithItems is always an array
        $clientsWithItems = $clientsWithItems ?? [];
    @endphp
    
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
                        <p class="text-gray-600 mt-2">{{ $servicePoint->branch->name ?? 'N/A' }} - Client Management</p>
                    </div>
                </div>
                <div class="flex space-x-6">
                    <div class="text-center">
                        <div class="text-sm text-gray-600">Total Clients</div>
                        <div class="text-2xl font-bold text-[#011478]">{{ count($clientsWithItems) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600">Total Items</div>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Client Management Section -->
                <div class="p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Client Service Queue</h4>
                        
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button onclick="switchTab('pending')" 
                                        id="tab-pending"
                                        class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm active">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                                        Pending Clients ({{ count(array_filter($clientsWithItems, function($client) { return isset($client['pending']) && count($client['pending']) > 0; })) }})
                                    </span>
                                </button>
                                <button onclick="switchTab('partially-done')" 
                                        id="tab-partially-done"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                                        In Progress Clients ({{ count(array_filter($clientsWithItems, function($client) { return isset($client['partially_done']) && count($client['partially_done']) > 0; })) }})
                                    </span>
                                </button>
                                <button onclick="switchTab('completed')" 
                                        id="tab-completed"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    <span class="flex items-center">
                                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                        Completed Clients ({{ count(array_filter($clientsWithItems, function($client) { return isset($client['completed']) && count($client['completed']) > 0; })) }})
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div id="tab-content">
                            <!-- Pending Clients Tab -->
                            <div id="content-pending" class="tab-content">
                                @php
                                    $pendingClients = array_filter($clientsWithItems, function($client) { 
                                        return isset($client['pending']) && count($client['pending']) > 0; 
                                    });
                                @endphp
                                
                                @if(count($pendingClients) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Client Name</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($pendingClients as $clientId => $clientData)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $clientData['client']->name ?? 'Unknown Client' }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="status-badge status-pending">Pending</span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <a href="{{ route('service-points.client-details', [$servicePoint, $clientId]) }}" 
                                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition-colors text-sm inline-block">
                                                                View Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No pending clients</div>
                                    </div>
                                @endif
                            </div>

                            <!-- In Progress Clients Tab -->
                            <div id="content-partially-done" class="tab-content hidden">
                                @php
                                    $inProgressClients = array_filter($clientsWithItems, function($client) { 
                                        return isset($client['partially_done']) && count($client['partially_done']) > 0; 
                                    });
                                @endphp
                                
                                @if(count($inProgressClients) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Client Name</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($inProgressClients as $clientId => $clientData)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $clientData['client']->name ?? 'Unknown Client' }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="status-badge status-partially-done">In Progress</span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <a href="{{ route('service-points.client-details', [$servicePoint, $clientId]) }}" 
                                                               class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded transition-colors text-sm inline-block">
                                                                View Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No clients in progress</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Completed Clients Tab -->
                            <div id="content-completed" class="tab-content hidden">
                                @php
                                    $completedClients = array_filter($clientsWithItems, function($client) { 
                                        return count($client['completed']) > 0; 
                                    });
                                @endphp
                                
                                @if(count($completedClients) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Client Name</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($completedClients as $clientId => $clientData)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900 font-medium">
                                                            {{ $clientData['client']->name ?? 'Unknown Client' }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="status-badge status-completed">Completed</span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <a href="{{ route('service-points.client-details', [$servicePoint, $clientId]) }}" 
                                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition-colors text-sm inline-block">
                                                                View Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                        <div class="text-gray-500">No completed clients</div>
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



        function moveToPartiallyDone(itemId) {
            // Show confirmation dialog with Sweet Alert
            Swal.fire({
                title: 'Start Service?',
                text: 'This will process money transfers and move the item to in progress status.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, start it!',
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
                        title: 'Service Started!',
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
                title: 'Complete Service?',
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
                        title: 'Service Completed!',
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

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</x-app-layout>

