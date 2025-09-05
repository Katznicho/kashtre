<div>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Service Points Management</h3>
                    <p class="text-sm text-gray-600 mt-1">Manage your service points and their queues</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="resetAllServicePointQueues()" 
                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset All Queues
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="switchServicePointTab('all', this)" 
                        id="tab-all"
                        class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm active">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        All Service Points
                    </span>
                </button>
                <button onclick="switchServicePointTab('active', this)" 
                        id="tab-active"
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Active Queues
                    </span>
                </button>
                <button onclick="switchServicePointTab('pending', this)" 
                        id="tab-pending"
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <span class="flex items-center">
                        <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                        Pending Items
                    </span>
                </button>
                <button onclick="switchServicePointTab('completed', this)" 
                        id="tab-completed"
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <span class="flex items-center">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        Completed Today
                    </span>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- All Service Points Tab -->
            <div id="content-all" class="tab-content">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Point</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->getTableQuery()->get() as $servicePoint)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $servicePoint->name }}</div>
                                        @if($servicePoint->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($servicePoint->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $servicePoint->business->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $servicePoint->branch->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $pendingCount = $servicePoint->pendingDeliveryQueues()->count();
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pendingCount > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $pendingCount }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewServicePointQueues({{ $servicePoint->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            View Queues
                                        </button>
                                        <button onclick="editServicePoint({{ $servicePoint->id }})" 
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        @if($pendingCount > 0)
                                            <button onclick="resetServicePointQueues({{ $servicePoint->id }}, '{{ $servicePoint->name }}')" 
                                                    class="text-red-600 hover:text-red-900 mr-3">
                                                Reset Queue
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Queues Tab -->
            <div id="content-active" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->getTableQuery()->get() as $servicePoint)
                        @php
                            $activeQueues = $servicePoint->serviceDeliveryQueues()->whereIn('status', ['pending', 'partially_done'])->count();
                        @endphp
                        @if($activeQueues > 0)
                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $servicePoint->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $activeQueues }} Active
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-3">
                                    <div>{{ $servicePoint->business->name ?? 'N/A' }} - {{ $servicePoint->branch->name ?? 'N/A' }}</div>
                                </div>
                                <button onclick="viewServicePointQueues({{ $servicePoint->id }})" 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors">
                                    View Queues
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Pending Items Tab -->
            <div id="content-pending" class="tab-content hidden">
                <div class="space-y-4">
                    @foreach($this->getTableQuery()->get() as $servicePoint)
                        @php
                            $pendingItems = $servicePoint->pendingDeliveryQueues()->with(['client', 'invoice'])->get();
                        @endphp
                        @if($pendingItems->count() > 0)
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $servicePoint->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $pendingItems->count() }} Pending
                                    </span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($pendingItems->take(5) as $item)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 text-gray-900">{{ $item->client->name ?? 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->item_name }}</td>
                                                    <td class="px-3 py-2 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-gray-600">{{ $item->queued_at ? $item->queued_at->format('H:i') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($pendingItems->count() > 5)
                                    <div class="mt-3 text-center">
                                        <button onclick="viewServicePointQueues({{ $servicePoint->id }})" 
                                                class="text-sm text-blue-600 hover:text-blue-800 underline">
                                            View All {{ $pendingItems->count() }} Pending Items
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Completed Today Tab -->
            <div id="content-completed" class="tab-content hidden">
                <div class="space-y-4">
                    @foreach($this->getTableQuery()->get() as $servicePoint)
                        @php
                            $completedItems = $servicePoint->serviceDeliveryQueues()->where('status', 'completed')->whereDate('completed_at', today())->with(['client', 'invoice'])->get();
                        @endphp
                        @if($completedItems->count() > 0)
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $servicePoint->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $completedItems->count() }} Completed
                                    </span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Completed At</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($completedItems->take(5) as $item)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 text-gray-900">{{ $item->client->name ?? 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->item_name }}</td>
                                                    <td class="px-3 py-2 text-gray-600">{{ $item->completed_at ? $item->completed_at->format('H:i') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($completedItems->count() > 5)
                                    <div class="mt-3 text-center">
                                        <button onclick="viewServicePointQueues({{ $servicePoint->id }})" 
                                                class="text-sm text-blue-600 hover:text-blue-800 underline">
                                            View All {{ $completedItems->count() }} Completed Items
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchServicePointTab(tabName, button) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Show the selected tab content
            const selectedContent = document.getElementById(`content-${tabName}`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }

            // Add active class to the selected tab button
            if (button) {
                button.classList.add('active', 'border-blue-500', 'text-blue-600');
                button.classList.remove('border-transparent', 'text-gray-500');
            }
        }

        function viewServicePointQueues(servicePointId) {
            // Redirect to the service queues page with the specific service point
            window.location.href = `/service-queues?service_point=${servicePointId}`;
        }

        function editServicePoint(servicePointId) {
            // Implement edit functionality
            console.log('Edit service point:', servicePointId);
        }

        function resetServicePointQueues(servicePointId, servicePointName) {
            if (!confirm(`Are you sure you want to reset all queued items for "${servicePointName}"? This action cannot be undone.`)) {
                return;
            }

            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Resetting...';

            fetch(`/service-delivery-queues/service-point/${servicePointId}/reset`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message using SweetAlert
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to show updated data
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to reset queues');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message using SweetAlert
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to reset queues. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Restore button state
                button.disabled = false;
                button.textContent = originalText;
            });
        }

        function resetAllServicePointQueues() {
            if (!confirm('Are you sure you want to reset ALL service point queues? This action cannot be undone and will affect all service points.')) {
                return;
            }

            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Resetting All...';

            fetch('/service-delivery-queues/reset-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message using SweetAlert
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to show updated data
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to reset all queues');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message using SweetAlert
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to reset all queues. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Restore button state
                button.disabled = false;
                button.textContent = originalText;
            });
        }
    </script>
</div>
