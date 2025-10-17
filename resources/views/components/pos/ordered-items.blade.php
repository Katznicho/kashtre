{{-- 
    Unified Ordered Items (Requests/Orders) Component
    This component serves as a single source of truth for displaying ordered items
    across both POS item selection and Service Point client details pages.
    It handles pending, partially done, and completed items with consistent styling.
--}}
{{-- This component serves as the single source of truth for displaying ordered items --}}

<!-- Section 5: Ordered Items (Requests/Orders) -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Ordered Items (Requests/Orders)</h3>
            <div class="text-right">
                <div class="text-sm text-gray-600">Total Amount</div>
                <div class="text-lg font-bold text-blue-600">
                    {{ number_format($correctTotalAmount ?? 0, 0) }} UGX
                </div>
            </div>
        </div>
        <form id="itemStatusForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Item Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Proforma Invoice</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Current Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if(isset($pendingItems) && $pendingItems->count() > 0)
                        @foreach($pendingItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    {{ $item->item->name ?? $item->item_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-600 font-semibold">{{ number_format($item->price, 0) }} UGX</td>
                                <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 font-semibold text-green-600">
                                    {{ number_format($item->price * $item->quantity, 0) }} UGX
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge status-pending">Pending</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="pending" class="mr-2">
                                            <span class="text-sm">Not Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="partially_done" class="mr-2" {{ $item->status == 'partially_done' ? 'checked' : '' }}>
                                            <span class="text-sm">Partially Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="completed" class="mr-2" {{ $item->status == 'completed' ? 'checked' : '' }}>
                                            <span class="text-sm">Completed (Done)</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    
                    @if(isset($partiallyDoneItems) && $partiallyDoneItems->count() > 0)
                        @foreach($partiallyDoneItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    {{ $item->item->name ?? $item->item_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-600 font-semibold">{{ number_format($item->price, 0) }} UGX</td>
                                <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 font-semibold text-green-600">
                                    {{ number_format($item->price * $item->quantity, 0) }} UGX
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge status-partially-done">In Progress</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="pending" class="mr-2">
                                            <span class="text-sm">Not Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="partially_done" class="mr-2" {{ $item->status == 'partially_done' ? 'checked' : '' }}>
                                            <span class="text-sm">Partially Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="completed" class="mr-2" {{ $item->status == 'completed' ? 'checked' : '' }}>
                                            <span class="text-sm">Completed (Done)</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    
                    @if(isset($completedItems) && $completedItems->count() > 0)
                        @foreach($completedItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    {{ $item->item->name ?? $item->item_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-600 font-semibold">{{ number_format($item->price, 0) }} UGX</td>
                                <td class="px-4 py-3 text-gray-600 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 font-semibold text-green-600">
                                    {{ number_format($item->price * $item->quantity, 0) }} UGX
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge status-completed">Completed</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="pending" class="mr-2">
                                            <span class="text-sm">Not Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="partially_done" class="mr-2" {{ $item->status == 'partially_done' ? 'checked' : '' }}>
                                            <span class="text-sm">Partially Done</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="item_statuses[{{ $item->id }}]" value="completed" class="mr-2" {{ $item->status == 'completed' ? 'checked' : '' }}>
                                            <span class="text-sm">Completed (Done)</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    
                    @if((!isset($pendingItems) || $pendingItems->count() == 0) && 
                        (!isset($partiallyDoneItems) || $partiallyDoneItems->count() == 0) && 
                        (!isset($completedItems) || $completedItems->count() == 0))
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No ordered items found for this client.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>
        </form>
    </div>
</div>
