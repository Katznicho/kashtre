<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Entity Service Charge</h1>
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

        <form action="{{ route('service-charges.update', $serviceCharge) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            @method('PUT')
            
            <!-- Entity Selection -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Entity Selection</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Entity Type*
                        </label>
                        <select id="entity_type" name="entity_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Entity Type</option>
                            <option value="business" {{ old('entity_type', $serviceCharge->entity_type) === 'business' ? 'selected' : '' }}>Business/Hospital</option>

                        </select>
                    </div>
                    <div>
                        <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Entity*
                        </label>
                        <select id="entity_id" name="entity_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Entity</option>
                            @if($serviceCharge->entity_type === 'business')
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('entity_id', $serviceCharge->entity_id) == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach

                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Service Charge Details -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Charge Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="lower_bound" class="block text-sm font-medium text-gray-700 mb-2">
                            Lower Bound
                        </label>
                        <input type="number" id="lower_bound" name="lower_bound" value="{{ old('lower_bound', $serviceCharge->lower_bound) }}" 
                               step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                    </div>
                    <div>
                        <label for="upper_bound" class="block text-sm font-medium text-gray-700 mb-2">
                            Upper Bound
                        </label>
                        <input type="number" id="upper_bound" name="upper_bound" value="{{ old('upper_bound', $serviceCharge->upper_bound) }}" 
                               step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Charge*
                        </label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', $serviceCharge->amount) }}" 
                               step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="0.00">
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Charge Type*
                        </label>
                        <select id="type" name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Type</option>
                            <option value="fixed" {{ old('type', $serviceCharge->type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage" {{ old('type', $serviceCharge->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea id="description" name="description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter description...">{{ old('description', $serviceCharge->description) }}</textarea>
                </div>
                
                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $serviceCharge->is_active) ? 'checked' : '' }} 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Service Charge
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const entityTypeSelect = document.getElementById('entity_type');
    const entityIdSelect = document.getElementById('entity_id');

    // Entity type change handler
    entityTypeSelect.addEventListener('change', function() {
        const entityType = this.value;
        entityIdSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!entityType) {
            entityIdSelect.innerHTML = '<option value="">Select Entity Type First</option>';
            return;
        }

        // Fetch entities based on type
        fetch(`/service-charges/get-entities?entity_type=${entityType}`)
            .then(response => response.json())
            .then(data => {
                entityIdSelect.innerHTML = '<option value="">Select Entity</option>';
                data.forEach(entity => {
                    const option = document.createElement('option');
                    option.value = entity.id;
                    option.textContent = entity.name;
                    entityIdSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching entities:', error);
                entityIdSelect.innerHTML = '<option value="">Error loading entities</option>';
            });
    });
});
</script>
</x-app-layout>
