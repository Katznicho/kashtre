<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Contractor Service Charge</h1>
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
            <form action="{{ route('contractor-service-charges.update', $contractorServiceCharge) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contractor_profile_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Contractor *
                        </label>
                        <select name="contractor_profile_id" id="contractor_profile_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a contractor</option>
                            @foreach($contractors as $contractor)
                                <option value="{{ $contractor->id }}" {{ old('contractor_profile_id', $contractorServiceCharge->contractor_profile_id) == $contractor->id ? 'selected' : '' }}>
                                    {{ $contractor->user->name }} - {{ $contractor->business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type *
                        </label>
                        <select name="type" id="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select type</option>
                            <option value="fixed" {{ old('type', $contractorServiceCharge->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ old('type', $contractorServiceCharge->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount *
                        </label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $contractorServiceCharge->amount) }}" placeholder="Enter amount (e.g., 1000 or 5.5)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="upper_bound" class="block text-sm font-medium text-gray-700 mb-2">
                            Upper Bound *
                        </label>
                        <input type="number" name="upper_bound" id="upper_bound" step="0.01" min="0" value="{{ old('upper_bound', $contractorServiceCharge->upper_bound) }}" placeholder="Enter upper limit (e.g., 50000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="lower_bound" class="block text-sm font-medium text-gray-700 mb-2">
                            Lower Bound *
                        </label>
                        <input type="number" name="lower_bound" id="lower_bound" step="0.01" min="0" value="{{ old('lower_bound', $contractorServiceCharge->lower_bound) }}" placeholder="Enter lower limit (e.g., 1000)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3" placeholder="Enter description (optional)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $contractorServiceCharge->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $contractorServiceCharge->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('contractor-service-charges.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Service Charge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
