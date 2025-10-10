<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create Business Withdrawal Settings</h1>
            <a href="{{ route('business-withdrawal-settings.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to List
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('business-withdrawal-settings.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            
            <!-- Business Selection -->
            <div class="mb-6">
                <label for="business_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Business <span class="text-red-500">*</span>
                </label>
                <select name="business_id" id="business_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a business</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Withdrawal Charges Repeater -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Withdrawal Charges</h2>
                    <button type="button" id="add-withdrawal-charge" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Withdrawal Charge
                    </button>
                </div>
                
                <div id="withdrawal-charges-container">
                    <!-- Withdrawal charge items will be added here -->
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('business-withdrawal-settings.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Withdrawal Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Withdrawal Charge Template (hidden) -->
<template id="withdrawal-charge-template">
    <div class="withdrawal-charge-item border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-md font-medium text-gray-900">Withdrawal Charge #<span class="item-number"></span></h3>
            <button type="button" class="remove-withdrawal-charge text-red-600 hover:text-red-800 font-bold">
                Remove
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Lower Bound (UGX) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="withdrawal_charges[INDEX][lower_bound]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upper Bound (UGX) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="withdrawal_charges[INDEX][upper_bound]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 50000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Charge Amount <span class="text-red-500">*</span>
                </label>
                <input type="number" name="withdrawal_charges[INDEX][charge_amount]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 1000 or 5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Charge Type <span class="text-red-500">*</span>
                </label>
                <select name="withdrawal_charges[INDEX][charge_type]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select Type</option>
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage (%)</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Description (Optional)
            </label>
            <textarea name="withdrawal_charges[INDEX][description]" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter description for this withdrawal charge tier"></textarea>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('add-withdrawal-charge');
    const container = document.getElementById('withdrawal-charges-container');
    const template = document.getElementById('withdrawal-charge-template');
    let itemIndex = 0;

    // Add withdrawal charge handler
    addButton.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.withdrawal-charge-item');
        
        // Update all input names with current index
        item.querySelectorAll('input, select, textarea').forEach(input => {
            input.name = input.name.replace('[INDEX]', `[${itemIndex}]`);
        });
        
        // Update item number
        item.querySelector('.item-number').textContent = itemIndex + 1;
        
        // Add remove handler
        item.querySelector('.remove-withdrawal-charge').addEventListener('click', function() {
            item.remove();
            updateItemNumbers();
        });
        
        container.appendChild(item);
        itemIndex++;
    });

    // Remove withdrawal charge handler
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-withdrawal-charge')) {
            e.target.closest('.withdrawal-charge-item').remove();
            updateItemNumbers();
        }
    });

    // Update item numbers
    function updateItemNumbers() {
        const items = container.querySelectorAll('.withdrawal-charge-item');
        items.forEach((item, index) => {
            item.querySelector('.item-number').textContent = index + 1;
        });
    }

    // Add first withdrawal charge by default
    addButton.click();
});
</script>
</x-app-layout>

