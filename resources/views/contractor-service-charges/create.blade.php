<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create Contractor Service Charges</h1>
            <a href="{{ route('contractor-service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('contractor-service-charges.store') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="contractor_profile_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Contractor *
                    </label>
                    <select name="contractor_profile_id" id="contractor_profile_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select a contractor</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}" {{ old('contractor_profile_id') == $contractor->id ? 'selected' : '' }}>
                                {{ $contractor->user ? $contractor->user->name : 'Unknown User' }} - {{ $contractor->business ? $contractor->business->name : 'Unknown Business' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Service Charges *
                    </label>
                    <div id="service-charges-container">
                        <div class="service-charge-item border border-gray-300 rounded-md p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                                    <input type="number" name="service_charges[0][amount]" step="0.01" min="0" placeholder="Enter amount (e.g., 1000 or 5.5)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                                    <select name="service_charges[0][type]" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="">Select type</option>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lower Bound *</label>
                                    <input type="number" name="service_charges[0][lower_bound]" step="0.01" min="0" placeholder="Enter lower limit (e.g., 1000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Upper Bound *</label>
                                    <input type="number" name="service_charges[0][upper_bound]" step="0.01" min="0" placeholder="Enter upper limit (e.g., 50000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-service-charge" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Another Service Charge
                    </button>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('contractor-service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Service Charges
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let serviceChargeIndex = 1;
    
    document.getElementById('add-service-charge').addEventListener('click', function() {
        const container = document.getElementById('service-charges-container');
        const newItem = document.createElement('div');
        newItem.className = 'service-charge-item border border-gray-300 rounded-md p-4 mb-4';
        
        newItem.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                    <input type="number" name="service_charges[${serviceChargeIndex}][amount]" step="0.01" min="0" placeholder="Enter amount (e.g., 1000 or 5.5)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="service_charges[${serviceChargeIndex}][type]" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select type</option>
                        <option value="fixed">Fixed Amount</option>
                        <option value="percentage">Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lower Bound *</label>
                    <input type="number" name="service_charges[${serviceChargeIndex}][lower_bound]" step="0.01" min="0" placeholder="Enter lower limit (e.g., 1000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upper Bound *</label>
                    <input type="number" name="service_charges[${serviceChargeIndex}][upper_bound]" step="0.01" min="0" placeholder="Enter upper limit (e.g., 50000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="remove-service-charge bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                    Remove
                </button>
            </div>
        `;
        
        container.appendChild(newItem);
        serviceChargeIndex++;
        
        // Add event listener to remove button
        newItem.querySelector('.remove-service-charge').addEventListener('click', function() {
            container.removeChild(newItem);
        });
    });
});
</script>
</x-app-layout>
