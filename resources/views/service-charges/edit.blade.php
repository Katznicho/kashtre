<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Service Charge</h1>
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
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Entity</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Entity Type
                        </label>
                        <select id="entity_type" name="entity_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Entity Type</option>
                            <option value="business" {{ $serviceCharge->entity_type === 'business' ? 'selected' : '' }}>Business</option>
                            <option value="branch" {{ $serviceCharge->entity_type === 'branch' ? 'selected' : '' }}>Branch</option>
                            <option value="service_point" {{ $serviceCharge->entity_type === 'service_point' ? 'selected' : '' }}>Service Point</option>
                        </select>
                    </div>
                    <div>
                        <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Entity
                        </label>
                        <select id="entity_id" name="entity_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Entity</option>
                            @if($serviceCharge->entity_type === 'business')
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ $serviceCharge->entity_id == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            @elseif($serviceCharge->entity_type === 'branch')
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $serviceCharge->entity_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            @elseif($serviceCharge->entity_type === 'service_point')
                                @foreach($servicePoints as $servicePoint)
                                    <option value="{{ $servicePoint->id }}" {{ $serviceCharge->entity_id == $servicePoint->id ? 'selected' : '' }}>
                                        {{ $servicePoint->name }}
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $serviceCharge->name) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount
                        </label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', $serviceCharge->amount) }}" 
                               step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type
                        </label>
                        <select id="type" name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Type</option>
                            <option value="fixed" {{ $serviceCharge->type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ $serviceCharge->type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ $serviceCharge->is_active ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea id="description" name="description" rows="3" 
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $serviceCharge->description) }}</textarea>
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

        if (entityType) {
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
        } else {
            entityIdSelect.innerHTML = '<option value="">Select Entity Type First</option>';
        }
    });
});
</script>
</x-app-layout>
