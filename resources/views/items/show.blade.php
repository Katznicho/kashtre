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

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Service Point</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->servicePoint->name ?? 'Not assigned' }}</p>
                        </div>

                        @if($item->contractor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Contractor</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $item->contractor->account_name }} ({{ $item->contractor->business->name }})</p>
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