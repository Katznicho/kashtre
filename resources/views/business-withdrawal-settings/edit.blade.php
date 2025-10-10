<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Business Withdrawal Setting</h1>
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

        <form action="{{ route('business-withdrawal-settings.update', $businessWithdrawalSetting->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            @method('PUT')
            
            <!-- Business Selection -->
            <div class="mb-6">
                <label for="business_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Business <span class="text-red-500">*</span>
                </label>
                <select name="business_id" id="business_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a business</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" {{ old('business_id', $businessWithdrawalSetting->business_id) == $business->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Charge Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="lower_bound" class="block text-sm font-medium text-gray-700 mb-2">
                        Lower Bound (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="lower_bound" id="lower_bound" step="0.01" min="0" value="{{ old('lower_bound', $businessWithdrawalSetting->lower_bound) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 0">
                </div>
                
                <div>
                    <label for="upper_bound" class="block text-sm font-medium text-gray-700 mb-2">
                        Upper Bound (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="upper_bound" id="upper_bound" step="0.01" min="0" value="{{ old('upper_bound', $businessWithdrawalSetting->upper_bound) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 50000">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="charge_amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Charge Amount <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="charge_amount" id="charge_amount" step="0.01" min="0" value="{{ old('charge_amount', $businessWithdrawalSetting->charge_amount) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g. 1000 or 5">
                </div>
                
                <div>
                    <label for="charge_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Charge Type <span class="text-red-500">*</span>
                    </label>
                    <select name="charge_type" id="charge_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Type</option>
                        <option value="fixed" {{ old('charge_type', $businessWithdrawalSetting->charge_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('charge_type', $businessWithdrawalSetting->charge_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description (Optional)
                </label>
                <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter description for this withdrawal charge tier">{{ old('description', $businessWithdrawalSetting->description) }}</textarea>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $businessWithdrawalSetting->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('business-withdrawal-settings.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Withdrawal Setting
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>

