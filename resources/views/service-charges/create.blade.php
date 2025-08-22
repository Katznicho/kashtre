<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create Entity Service Charges</h1>
            <a href="{{ route('service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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

        <form action="{{ route('service-charges.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            
            <!-- Entity Selection -->
            <div class="mb-6">
                <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Entity *
                </label>
                <select name="entity_id" id="entity_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select an entity</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" {{ old('entity_id') == $business->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Service Charges Repeater -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Service Charges</h2>
                    <button type="button" id="add-service-charge" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Service Charge
                    </button>
                </div>
                
                <div id="service-charges-container">
                    <!-- Service charge items will be added here -->
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Service Charges
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Service Charge Template (hidden) -->
<template id="service-charge-template">
    <div class="service-charge-item border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-md font-medium text-gray-900">Service Charge #<span class="item-number"></span></h3>
            <button type="button" class="remove-service-charge text-red-600 hover:text-red-800 font-bold">
                Remove
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Lower Bound *
                </label>
                <input type="number" name="service_charges[INDEX][lower_bound]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="Enter lower bound">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upper Bound *
                </label>
                <input type="number" name="service_charges[INDEX][upper_bound]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="Enter upper bound">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Charge *
                </label>
                <input type="number" name="service_charges[INDEX][amount]" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="Enter amount">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Service Charge Type *
                </label>
                <select name="service_charges[INDEX][type]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select Type</option>
                    <option value="fixed">Fixed</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>
        </div>

    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('add-service-charge');
    const container = document.getElementById('service-charges-container');
    const template = document.getElementById('service-charge-template');
    let itemIndex = 0;

    // Add service charge handler
    addButton.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.service-charge-item');
        
        // Update all input names with current index
        item.querySelectorAll('input, select, textarea').forEach(input => {
            input.name = input.name.replace('[INDEX]', `[${itemIndex}]`);
        });
        
        // Update item number
        item.querySelector('.item-number').textContent = itemIndex + 1;
        
        // Add remove handler
        item.querySelector('.remove-service-charge').addEventListener('click', function() {
            item.remove();
            updateItemNumbers();
        });
        
        container.appendChild(item);
        itemIndex++;
    });

    // Remove service charge handler
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-service-charge')) {
            e.target.closest('.service-charge-item').remove();
            updateItemNumbers();
        }
    });

    // Update item numbers
    function updateItemNumbers() {
        const items = container.querySelectorAll('.service-charge-item');
        items.forEach((item, index) => {
            item.querySelector('.item-number').textContent = index + 1;
        });
    }

    // Add first service charge by default
    addButton.click();
});
</script>
</x-app-layout>
