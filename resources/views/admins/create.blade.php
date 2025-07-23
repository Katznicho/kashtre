@php
    use App\Models\Business;
    use App\Models\ServicePoint;

    $businessId = 1;
    $business = Business::with('branches')->findOrFail($businessId);
    $branch = $business->branches->first();
    $servicePoints = ServicePoint::where('business_id', $businessId)->get();
@endphp

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Create New Admin</h2>

                <form action="{{ route('admins.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Bio Section -->
                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Bio</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="surname">Surname <span class="text-red-500">*</span></label>
                                    <input type="text" name="surname" id="surname" required class="form-input w-full" placeholder="Enter surname">
                                </div>
                                <div>
                                    <label for="first_name">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" id="first_name" required class="form-input w-full" placeholder="Enter first name">
                                </div>
                                <div>
                                    <label for="middle_name">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" class="form-input w-full" placeholder="Enter middle name (optional)">
                                </div>
                            </div>
                            <div>
                                <label for="email">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" required class="form-input w-full" placeholder="Enter email address">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="phone">Phone <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone" id="phone" required class="form-input w-full" placeholder="Enter phone number">
                                </div>
                                <div>
                                    <label for="nin">NIN <span class="text-red-500">*</span></label>
                                    <input type="text" name="nin" id="nin" required class="form-input w-full" placeholder="Enter NIN">
                                </div>
                            </div>
                            <div>
                                <label for="gender">Gender <span class="text-red-500">*</span></label>
                                <select name="gender" id="gender" required class="form-select w-full">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="status">Status <span class="text-red-500">*</span></label>
                                <select name="status" id="status" required class="form-select w-full">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label for="profile_photo_path">Profile Photo</label>
                                <input type="file" name="profile_photo_path" id="profile_photo_path" accept="image/*" class="form-input w-full" placeholder="Upload profile photo">
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Permissions</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Permissions <span class="text-red-500">*</span></label>
                                <div class="mt-2 space-y-2">
                                    @foreach($permissions as $module => $subModules)
                                        <div class="pl-4">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="permissions_menu[]" value="{{ $module }}" class="module-checkbox form-checkbox h-5 w-5 text-indigo-600" {{ in_array($module, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                <span class="ml-2 font-bold text-lg text-gray-800">{{ $module }}</span>
                                            </label>
                                            @if(is_array($subModules) && !empty($subModules))
                                                <div class="ml-8 mt-1 space-y-1">
                                                    @foreach($subModules as $subModule => $actions)
                                                        <div>
                                                            <label class="inline-flex items-center">
                                                                <input type="checkbox" name="permissions_menu[]" value="{{ $subModule }}" class="submodule-checkbox form-checkbox h-5 w-5 text-indigo-600" {{ in_array($subModule, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                                <span class="ml-2 font-semibold text-base text-gray-700">{{ $subModule }}</span>
                                                            </label>
                                                            @if(is_array($actions) && !empty($actions))
                                                                <div class="ml-8 mt-1 space-y-1">
                                                                    @foreach($actions as $action)
                                                                        <div>
                                                                            <label class="inline-flex items-center">
                                                                                <input type="checkbox" name="permissions_menu[]" value="{{ $action }}" class="action-checkbox form-checkbox h-4 w-4 text-indigo-600" {{ in_array($action, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                                                <span class="ml-2 text-sm text-gray-600">{{ $action }}</span>
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @error('permissions_menu')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                    <div class="flex justify-end space-x-4 pt-4">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        // Module checkbox: Check/uncheck all submodules and actions
        $('.module-checkbox').on('change', function() {
            $(this).closest('div').find('input[type="checkbox"]').prop('checked', this.checked);
        });

        // Submodule checkbox: Check/uncheck all actions and update module checkbox
        $('.submodule-checkbox').on('change', function() {
            $(this).closest('div').find('.action-checkbox').prop('checked', this.checked);
            updateModuleCheckbox($(this));
        });

        // Action checkbox: Update submodule and module checkboxes
        $('.action-checkbox').on('change', function() {
            var submoduleCheckbox = $(this).closest('div').closest('div').find('.submodule-checkbox');
            var allActions = submoduleCheckbox.closest('div').find('.action-checkbox');
            var allChecked = allActions.length === allActions.filter(':checked').length;
            var anyChecked = allActions.filter(':checked').length > 0;

            submoduleCheckbox.prop('checked', allChecked);
            updateModuleCheckbox(submoduleCheckbox);
        });
    });
</script>

</x-app-layout>
