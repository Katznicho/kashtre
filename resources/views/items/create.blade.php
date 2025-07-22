
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Create New Item</h2>

                <form method="POST" action="{{ route('items.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(Auth::user()->business_id == 1)
                        <!-- Business -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business</label>
                            <select name="business_id" id="business_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                <option value="" disabled selected>Select business</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="business_id" value="{{ Auth::user()->business_id }}">
                        @endif

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item name">
                        </div>

                        <!-- Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <input type="text" name="code" id="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="Enter item code">
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                <option value="" disabled selected>Select type</option>
                                <option value="service">Service</option>
                                <option value="good">Good</option>
                                <option value="package">Package</option>
                                <option value="bulk">Bulk</option>
                            </select>
                        </div>

                        <!-- Default Price -->
                        <div>
                            <label for="default_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Price</label>
                            <input type="number" name="default_price" id="default_price" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="0.00">
                        </div>

                        <!-- Hospital Share -->
                        <div>
                            <label for="hospital_share" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hospital Share (%)</label>
                            <input type="number" name="hospital_share" id="hospital_share" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required value="100" placeholder="100">
                        </div>

                        <!-- Group -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subgroup -->
                        <div>
                            <label for="subgroup_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subgroup</label>
                            <select name="subgroup_id" id="subgroup_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                            <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- UOM -->
                        <div>
                            <label for="uom_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure</label>
                            <select name="uom_id" id="uom_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($itemUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Point -->
                        <div>
                            <label for="service_point_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Point</label>
                            <select name="service_point_id" id="service_point_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($servicePoints as $point)
                                    <option value="{{ $point->id }}">{{ $point->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Contractor -->
                        <div id="contractor_div" style="display: none;">
                            <label for="contractor_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contractor</label>
                            <select name="contractor_account_id" id="contractor_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">None</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Other Name -->
                        <div class="md:col-span-2">
                            <label for="other_names" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Other Name</label>
                            <input type="text" name="other_names" id="other_names" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter other name">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Enter description"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('items.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            Create Item
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

