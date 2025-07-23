@php
    use App\Models\Business;

    $businessId = $admin->business_id;
    $business = Business::with('branches')->findOrFail($businessId);
    $branch = $business->branches->first();
@endphp

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Edit Admin</h2>

                <form action="{{ route('admins.update', $admin->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Bio Section -->
                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Bio</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="surname">Surname</label>
                                    <input type="text" name="surname" id="surname" class="form-input w-full" required value="{{ old('surname', $admin->surname) }}">
                                </div>
                                <div>
                                    <label for="first_name">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-input w-full" required value="{{ old('first_name', $admin->first_name) }}">
                                </div>
                                <div>
                                    <label for="middle_name">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" class="form-input w-full" value="{{ old('middle_name', $admin->middle_name) }}">
                                </div>
                            </div>
                            <div>
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-input w-full" required value="{{ old('email', $admin->email) }}">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-input w-full" required value="{{ old('phone', $admin->phone) }}">
                                </div>
                                <div>
                                    <label for="nin">NIN</label>
                                    <input type="text" name="nin" id="nin" class="form-input w-full" required value="{{ old('nin', $admin->nin) }}">
                                </div>
                            </div>
                            <div>
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" class="form-select w-full" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $admin->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $admin->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $admin->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-select w-full" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $admin->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $admin->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $admin->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label for="profile_photo_path">Profile Photo</label>
                                <input type="file" name="profile_photo_path" id="profile_photo_path" class="form-input w-full">
                                @if($admin->profile_photo_path)
                                    <img src="{{ asset('storage/' . $admin->profile_photo_path) }}" alt="Profile Photo" class="mt-2 w-20 h-20 rounded-full">
                                @endif
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
                                @php $adminPermissions = old('permissions_menu', $admin->permissions ?? []); @endphp
                                @foreach($permissions as $module => $subModules)
                                    <div class="pl-4">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="permissions_menu[]" value="{{ $module }}" class="module-checkbox form-checkbox h-5 w-5"
                                                {{ in_array($module, $adminPermissions) ? 'checked' : '' }}>
                                            <span class="ml-2 font-bold text-lg text-gray-800">{{ $module }}</span>
                                        </label>
                                        @if(is_array($subModules))
                                            <div class="ml-8 mt-1 space-y-1">
                                                @foreach($subModules as $subModule => $actions)
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" name="permissions_menu[]" value="{{ $subModule }}" class="submodule-checkbox form-checkbox h-5 w-5"
                                                            {{ in_array($subModule, $adminPermissions) ? 'checked' : '' }}>
                                                        <span class="ml-2 font-semibold text-base text-gray-700">{{ $subModule }}</span>
                                                    </label>
                                                    @if(is_array($actions))
                                                        <div class="ml-8 mt-1 space-y-1">
                                                            @foreach($actions as $action)
                                                                <label class="inline-flex items-center">
                                                                    <input type="checkbox" name="permissions_menu[]" value="{{ $action }}" class="action-checkbox form-checkbox h-4 w-4"
                                                                        {{ in_array($action, $adminPermissions) ? 'checked' : '' }}>
                                                                    <span class="ml-2 text-sm text-gray-600">{{ $action }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                    <div class="flex justify-end space-x-4 pt-4">
                        <a href="{{ route('admins.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">Update Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery logic reused -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.module-checkbox').on('change', function () {
                $(this).closest('div').find('input[type="checkbox"]').prop('checked', this.checked);
            });

            $('.submodule-checkbox').on('change', function () {
                $(this).closest('div').find('.action-checkbox').prop('checked', this.checked);
                updateModuleCheckbox($(this));
            });

            $('.action-checkbox').on('change', function () {
                var submoduleCheckbox = $(this).closest('div').closest('div').find('.submodule-checkbox');
                var allActions = submoduleCheckbox.closest('div').find('.action-checkbox');
                var allChecked = allActions.length === allActions.filter(':checked').length;
                submoduleCheckbox.prop('checked', allChecked);
                updateModuleCheckbox(submoduleCheckbox);
            });

            function updateModuleCheckbox($submoduleCheckbox) {
                var moduleCheckbox = $submoduleCheckbox.closest('div').closest('div').closest('div').find('.module-checkbox');
                var allSubmodules = moduleCheckbox.closest('div').find('.submodule-checkbox');
                var anyChecked = allSubmodules.filter(':checked').length > 0;
                moduleCheckbox.prop('checked', anyChecked);
            }
        });
    </script>
</x-app-layout>
