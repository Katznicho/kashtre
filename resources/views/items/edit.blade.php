
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Edit Item</h2>

                <form method="POST" action="{{ route('items.update', $item) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(Auth::user()->business_id == 1)
                        <!-- Business -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business</label>
                            <select name="business_id" id="business_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id', $item->business_id) == $business->id ? 'selected' : '' }}>{{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="business_id" value="{{ $item->business_id }}">
                        @endif

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item name">
                        </div>

                        <!-- Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $item->code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item code">
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                <option value="service" {{ old('type', $item->type) == 'service' ? 'selected' : '' }}>Service</option>
                                <option value="good" {{ old('type', $item->type) == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="package" {{ old('type', $item->type) == 'package' ? 'selected' : '' }}>Package</option>
                                <option value="bulk" {{ old('type', $item->type) == 'bulk' ? 'selected' : '' }}>Bulk</option>
                            </select>
                        </div>

                        <!-- Default Price -->
                        <div>
                            <label for="default_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Price</label>
                            <input type="number" name="default_price" id="default_price" value="{{ old('default_price', $item->default_price) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="0.00">
                        </div>

                        <!-- Hospital Share -->
                        <div>
                            <label for="hospital_share" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hospital Share (%)</label>
                            <input type="number" name="hospital_share" id="hospital_share" value="{{ old('hospital_share', $item->hospital_share) }}" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="100">
                        </div>

                        <!-- Group -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id', $item->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subgroup -->
                        <div>
                            <label for="subgroup_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subgroup</label>
                            <select name="subgroup_id" id="subgroup_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('subgroup_id', $item->subgroup_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                            <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $item->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- UOM -->
                        <div>
                            <label for="uom_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure</label>
                            <select name="uom_id" id="uom_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($itemUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ old('uom_id', $item->uom_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Point -->
                        <div>
                            <label for="service_point_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Point</label>
                            <select name="service_point_id" id="service_point_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($servicePoints as $point)
                                    <option value="{{ $point->id }}" {{ old('service_point_id', $item->service_point_id) == $point->id ? 'selected' : '' }}>{{ $point->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Contractor -->
                        <div id="contractor_div" @if(old('hospital_share', $item->hospital_share) == 100) style="display: none;" @endif>
                            <label for="contractor_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contractor</label>
                            <select name="contractor_account_id" id="contractor_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('contractor_account_id', $item->contractor_account_id) == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Other Name -->
                        <div class="md:col-span-2">
                            <label for="other_names" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Name</label>
                            <input type="text" name="other_names" id="other_names" value="{{ old('other_names', $item->other_names) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter other name">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter description">{{ old('description', $item->description) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('items.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hospitalShare = document.getElementById('hospital_share');
            const contractorDiv = document.getElementById('contractor_div');

            function toggleContractor() {
                if (hospitalShare.value !== '100') {
                    contractorDiv.style.display = 'block';
                } else {
                    contractorDiv.style.display = 'none';
                }
            }

            hospitalShare.addEventListener('input', toggleContractor);
            toggleContractor(); // Initial check
        });
    </script>
</x-app-layout>

