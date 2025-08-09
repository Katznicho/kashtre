<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Item Details</h2>
                    <div class="flex space-x-2">
                        @if(in_array('Edit Items', Auth::user()->permissions ?? []))
                        <a href="{{ route('items.edit', $item) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition duration-150">
                            Edit Item
                        </a>
                        @endif
                        <a href="{{ route('items.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-400 transition duration-150">
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">Basic Information</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Code</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->code }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Type</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($item->type) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Business</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->business->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Default Price</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">UGX {{ number_format($item->default_price, 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Hospital Share</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->hospital_share }}%</p>
                        </div>

                        @if($item->other_names)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Other Names</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->other_names }}</p>
                        </div>
                        @endif

                        @if($item->contractor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Contractor</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->contractor->account_name }} ({{ $item->contractor->business->name }})</p>
                        </div>
                        @endif
                    </div>

                    <!-- Categorization -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">Categorization</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Group</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->group->name ?? 'Not assigned' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Subgroup</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->subgroup->name ?? 'Not assigned' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Department</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->department->name ?? 'Not assigned' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Unit of Measure</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->itemUnit->name ?? 'Not assigned' }}</p>
                        </div>

                        @if($item->branchServicePoints->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Branch Service Points</label>
                            <div class="mt-1 space-y-1">
                                @foreach($item->branchServicePoints as $branchServicePoint)
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <span class="font-medium">{{ $branchServicePoint->branch->name }}:</span> 
                                        {{ $branchServicePoint->servicePoint->name }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Branch Service Points</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">No service points assigned</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                @if($item->description)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Description</h3>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $item->description }}</p>
                </div>
                @endif

                <!-- Package Items -->
                @if($item->type === 'package' && $item->packageItems->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Package Items</h3>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-3">
                            <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Package Validity: {{ $item->validity_days }} days</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($item->packageItems as $packageItem)
                        <div class="border rounded-lg p-4 bg-blue-50 dark:bg-blue-900/20">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $packageItem->includedItem->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $packageItem->includedItem->code }}</p>
                                </div>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Max: {{ $packageItem->max_quantity }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                <p>Type: {{ ucfirst($packageItem->includedItem->type) }}</p>
                                <p>Price: UGX {{ number_format($packageItem->includedItem->default_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Bulk Items -->
                @if($item->type === 'bulk' && $item->bulkItems->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Bulk Items</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($item->bulkItems as $bulkItem)
                        <div class="border rounded-lg p-4 bg-green-50 dark:bg-green-900/20">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $bulkItem->includedItem->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bulkItem->includedItem->code }}</p>
                                </div>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">Qty: {{ $bulkItem->fixed_quantity }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                <p>Type: {{ ucfirst($bulkItem->includedItem->type) }}</p>
                                <p>Price: UGX {{ number_format($bulkItem->includedItem->default_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Included In Packages -->
                @if($item->includedInPackages->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Included In Packages</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($item->includedInPackages as $packageInclusion)
                        <div class="border rounded-lg p-4 bg-purple-50 dark:bg-purple-900/20">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $packageInclusion->packageItem->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $packageInclusion->packageItem->code }}</p>
                                </div>
                                <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">Max: {{ $packageInclusion->max_quantity }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                <p>Package Type: {{ ucfirst($packageInclusion->packageItem->type) }}</p>
                                <p>Package Price: UGX {{ number_format($packageInclusion->packageItem->default_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Included In Bulks -->
                @if($item->includedInBulks->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Included In Bulks</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($item->includedInBulks as $bulkInclusion)
                        <div class="border rounded-lg p-4 bg-orange-50 dark:bg-orange-900/20">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $bulkInclusion->bulkItem->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bulkInclusion->bulkItem->code }}</p>
                                </div>
                                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">Qty: {{ $bulkInclusion->fixed_quantity }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                <p>Bulk Type: {{ ucfirst($bulkInclusion->bulkItem->type) }}</p>
                                <p>Bulk Price: UGX {{ number_format($bulkInclusion->bulkItem->default_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Branch Pricing -->
                @if($item->branchPrices->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-4">Branch-Specific Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($item->branchPrices as $branchPrice)
                        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $branchPrice->branch->name }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">UGX {{ number_format($branchPrice->price, 2) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <div>
                            <label class="block font-medium">Created</label>
                            <p>{{ $item->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block font-medium">Last Updated</label>
                            <p>{{ $item->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($item->deleted_at)
                        <div>
                            <label class="block font-medium text-red-600">Deleted</label>
                            <p class="text-red-600">{{ $item->deleted_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 