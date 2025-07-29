@php
use App\Models\Business;
use App\Models\Qualification;
use App\Models\Department;
use App\Models\Section;
use App\Models\Title;
use App\Models\ServicePoint;

$businesses = Business::with('branches')->where('id', '!=', 1)->get()->keyBy('id');

$businessBranchData = $businesses->map(function ($b) {
return [
'id' => $b->id,
'branches' => $b->branches->map(function ($br) {
return [
'id' => $br->id,
'name' => $br->name,
];
})->values()->all(),
];
})->values()->all();

$qualifications = Qualification::all();
$departments = Department::all();
$sections = Section::all();
$titles = Title::all();
// Group by business_id for Alpine.js
$qualificationsByBusiness = $qualifications->groupBy('business_id')->map(function($items) {
return $items->map(function($item) {
return ['id' => $item->id, 'name' => $item->name];
})->values();
});
$departmentsByBusiness = $departments->groupBy('business_id')->map(function($items) {
return $items->map(function($item) {
return ['id' => $item->id, 'name' => $item->name];
})->values();
});
$sectionsByBusiness = $sections->groupBy('business_id')->map(function($items) {
return $items->map(function($item) {
return ['id' => $item->id, 'name' => $item->name];
})->values();
});
$titlesByBusiness = $titles->groupBy('business_id')->map(function($items) {
return $items->map(function($item) {
return ['id' => $item->id, 'name' => $item->name];
})->values();
});
$servicePoints = ServicePoint::all();
// Group service points by business_id for Alpine.js
$servicePointsByBusiness = $servicePoints->groupBy('business_id')->map(function($sps) {
return $sps->map(function($sp) {
return ['id' => $sp->id, 'name' => $sp->name];
})->values();
});
@endphp

<x-app-layout>
    <div class="py-12" x-data="userForm()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Create New User</h2>

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Bio</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <!-- Bio fields start -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="surname">Surname <span class="text-red-500">*</span></label>
                                    <input type="text" name="surname" id="surname" required placeholder="Enter surname" class="form-input w-full">
                                </div>
                                <div>
                                    <label for="first_name">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" id="first_name" required placeholder="Enter first name" class="form-input w-full">
                                </div>
                                <div>
                                    <label for="middle_name">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" placeholder="Enter middle name (optional)" class="form-input w-full">
                                </div>
                            </div>
                            <div>
                                <label for="email">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" required placeholder="Enter email address" class="form-input w-full">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="phone">Phone <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone" id="phone" required placeholder="Enter phone number" class="form-input w-full">
                                </div>
                                <div>
                                    <label for="nin">NIN <span class="text-red-500">*</span></label>
                                    <input type="text" name="nin" id="nin" required placeholder="Enter NIN" class="form-input w-full">
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
                                <label for="profile_photo_path">Profile Photo </label>
                                <input type="file" name="profile_photo_path" id="profile_photo_path" accept="image/*" class="form-input w-full" placeholder="Upload profile photo">
                            </div>


                            <!-- Bio fields end -->
                        </div>
                    </div>

                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Business Info</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <!-- Business Info fields start -->
                            <div>
                                <label for="business_id">Business <span class="text-red-500">*</span></label>
                                <select name="business_id" id="business_id" required class="form-select w-full" x-model="selectedBusinessId" @change="updateBranches">
                                    <option value="" disabled selected>Select Business</option>
                                    @foreach($businesses as $id => $business)
                                    <option value="{{ $id }}">{{ $business->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="branch_id">Branch <span class="text-red-500">*</span></label>
                                <select name="branch_id" id="branch_id" required class="form-select w-full" :disabled="!branches.length">
                                    <option value="" disabled selected>Select Branch</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="qualification_id">Qualification <span class="text-red-500">*</span></label>
                                    <select name="qualification_id" id="qualification_id" required class="form-select w-full">
                                        <option value="" disabled selected>Select Qualification</option>
                                        <template x-for="q in filteredQualifications" :key="q.id">
                                            <option :value="q.id" x-text="q.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label for="title_id">Title <span class="text-red-500">*</span></label>
                                    <select name="title_id" id="title_id" required class="form-select w-full">
                                        <option value="" disabled selected>Select Title</option>
                                        <template x-for="t in filteredTitles" :key="t.id">
                                            <option :value="t.id" x-text="t.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label for="department_id">Department <span class="text-red-500">*</span></label>
                                    <select name="department_id" id="department_id" required class="form-select w-full">
                                        <option value="" disabled selected>Select Department</option>
                                        <template x-for="d in filteredDepartments" :key="d.id">
                                            <option :value="d.id" x-text="d.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label for="section_id">Section <span class="text-red-500">*</span></label>
                                    <select name="section_id" id="section_id" required class="form-select w-full">
                                        <option value="" disabled selected>Select Section</option>
                                        <template x-for="s in filteredSections" :key="s.id">
                                            <option :value="s.id" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>
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
                            <!-- Business Info fields end -->
                        </div>
                    </div>

                    <div x-data="{ open: true }" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Permissions</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <!-- Permissions fields start -->
                            <div>
                                <label for="service_points">Service Points <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <template x-for="sp in filteredServicePoints" :key="sp.id">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="service_points[]" :value="sp.name" class="form-checkbox">
                                            <span class="ml-2" x-text="sp.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label for="allowed_branches">Allowed Branches <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <template x-for="branch in filteredBranches" :key="branch.id">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="allowed_branches[]" :value="branch.id" class="form-checkbox">
                                            <span class="ml-2" x-text="branch.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <!-- Permissions -->
                            <div x-data="{ open: true }" class="mb-4 border rounded">
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                                    <span>Permissions</span>
                                    <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" class="p-4 space-y-6">

                                    <!-- Service Points -->
                                    <div>
                                        <label for="service_points">Service Points <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <template x-for="sp in filteredServicePoints" :key="sp.id">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="service_points[]" :value="sp.name" class="form-checkbox">
                                                    <span class="ml-2" x-text="sp.name"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Allowed Branches -->
                                    <div>
                                        <label for="allowed_branches">Allowed Branches <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <template x-for="branch in filteredBranches" :key="branch.id">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="allowed_branches[]" :value="branch.id" class="form-checkbox">
                                                    <span class="ml-2" x-text="branch.name"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Permissions -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Permissions <span class="text-red-500">*</span>
                                        </label>

                                        @foreach ($app_permissions as $group => $categories)
                                        <div class="mb-6 border p-4 rounded">
                                            {{-- Group Checkbox --}}
                                            <label class="inline-flex items-center mb-3">
                                                <input type="checkbox" name="permissions_menu[]" value="{{ $group }}" class="module-checkbox form-checkbox h-5 w-5 text-indigo-600" {{ in_array($group, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                <span class="ml-2 font-bold text-lg text-gray-800">{{ $group }}</span>
                                            </label>

                                            {{-- Categories under Group --}}
                                            @foreach ($categories as $category => $perms)
                                            <div class="pl-8 mb-4 border-l-2 border-gray-300">
                                                {{-- Category Checkbox --}}
                                                <label class="inline-flex items-center mb-2">
                                                    <input type="checkbox" name="permissions_menu[]" value="{{ $category }}" class="submodule-checkbox form-checkbox h-4 w-4 text-indigo-600" {{ in_array($category, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                    <span class="ml-2 font-semibold text-gray-700">{{ $category }}</span>
                                                </label>

                                                {{-- Permission checkboxes --}}
                                                <div class="ml-6 space-y-1">
                                                    @foreach ($perms as $permission)
                                                    <label class="inline-flex items-center space-x-2">
                                                        <input type="checkbox" name="permissions_menu[]" value="{{ $permission }}" class="action-checkbox form-checkbox h-3 w-3 text-indigo-600" {{ in_array($permission, old('permissions_menu', [])) ? 'checked' : '' }}>
                                                        <span>{{ $permission }}</span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endforeach

                                        @error('permissions_menu')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions fields end -->
                        </div>
                    </div>

                    <div x-data="{ open: true }" x-show="isContractorSelected" class="mb-4 border rounded">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left text-lg font-semibold focus:outline-none">
                            <span>Contractor Profile</span>
                            <svg :class="{'rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" class="p-4 space-y-4">
                            <div>
                                <label for="bank_name">Bank Name <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" placeholder="Enter bank name" class="form-input w-full">
                            </div>
                            <div>
                                <label for="account_name">Account Name <span class="text-red-500">*</span></label>
                                <input type="text" name="account_name" id="account_name" placeholder="Enter account name" class="form-input w-full">
                            </div>
                            <div>
                                <label for="account_number">Account Number <span class="text-red-500">*</span></label>
                                <input type="text" name="account_number" id="account_number" placeholder="Enter account number" class="form-input w-full">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // When module (group) checkbox changes
            $('.module-checkbox').on('change', function() {
                // Check/uncheck all submodules and actions under this group
                $(this).closest('div.mb-6').find('input[type="checkbox"]').prop('checked', this.checked);
            });

            // When submodule (category) checkbox changes
            $('.submodule-checkbox').on('change', function() {
                // Check/uncheck all action checkboxes under this category
                $(this).closest('div.pl-8').find('.action-checkbox').prop('checked', this.checked);

                // Update group checkbox state (if all submodules checked => checked)
                updateModuleCheckbox($(this));
            });

            // When action checkbox changes
            $('.action-checkbox').on('change', function() {
                var $categoryDiv = $(this).closest('div.pl-8');
                var $submoduleCheckbox = $categoryDiv.find('.submodule-checkbox');
                var allActions = $categoryDiv.find('.action-checkbox');
                var allChecked = allActions.length === allActions.filter(':checked').length;

                $submoduleCheckbox.prop('checked', allChecked);

                updateModuleCheckbox($submoduleCheckbox);
            });

            function updateModuleCheckbox($element) {
                var $groupDiv = $element.closest('div.mb-6');
                var allSubmodules = $groupDiv.find('.submodule-checkbox');
                var allChecked = allSubmodules.length === allSubmodules.filter(':checked').length;
                var anyChecked = allSubmodules.filter(':checked').length > 0;
                var $moduleCheckbox = $groupDiv.find('.module-checkbox').first();

                $moduleCheckbox.prop('checked', allChecked);
            }
        });

    </script>
    <script>
        function userForm() {
            return {
                selectedBusinessId: ''
                , branches: []
                , businessData: @json($businessBranchData)
                , servicePointsByBusiness: @json($servicePointsByBusiness)
                , allBranches: @json($businesses-> map->branches)
                , qualificationsByBusiness: @json($qualificationsByBusiness)
                , departmentsByBusiness: @json($departmentsByBusiness)
                , sectionsByBusiness: @json($sectionsByBusiness)
                , titlesByBusiness: @json($titlesByBusiness)
                , filteredServicePoints: []
                , filteredBranches: []
                , filteredQualifications: []
                , filteredDepartments: []
                , filteredSections: []
                , filteredTitles: []
                , isContractorSelected: false
                , init() {
                    this.updateBranches();
                    this.updateServicePointsAndBranches();
                    this.$nextTick(() => {
                        this.watchContractorPermission();
                    });
                }
                , watchContractorPermission() {
                    const self = this;

                    function checkContractor() {
                        const checked = Array.from(document.querySelectorAll('input[name=\'permissions_menu[]\']:checked')).map(cb => cb.value);
                        self.isContractorSelected = checked.includes('Contractor');
                    }
                    checkContractor();
                    document.querySelectorAll('input[name=\'permissions_menu[]\']').forEach(cb => {
                        cb.addEventListener('change', checkContractor);
                    });
                }
                , updateBranches() {
                    const biz = this.businessData.find(b => b.id == this.selectedBusinessId);
                    this.branches = biz ? biz.branches : [];
                    this.updateServicePointsAndBranches();
                }
                , updateServicePointsAndBranches() {
                    this.filteredServicePoints = this.selectedBusinessId && this.servicePointsByBusiness[this.selectedBusinessId] ?
                        this.servicePointsByBusiness[this.selectedBusinessId] : [];
                    const biz = this.businessData.find(b => b.id == this.selectedBusinessId);
                    this.filteredBranches = biz ? biz.branches : [];
                    this.filteredQualifications = this.selectedBusinessId && this.qualificationsByBusiness[this.selectedBusinessId] ?
                        this.qualificationsByBusiness[this.selectedBusinessId] : [];
                    this.filteredDepartments = this.selectedBusinessId && this.departmentsByBusiness[this.selectedBusinessId] ?
                        this.departmentsByBusiness[this.selectedBusinessId] : [];
                    this.filteredSections = this.selectedBusinessId && this.sectionsByBusiness[this.selectedBusinessId] ?
                        this.sectionsByBusiness[this.selectedBusinessId] : [];
                    this.filteredTitles = this.selectedBusinessId && this.titlesByBusiness[this.selectedBusinessId] ?
                        this.titlesByBusiness[this.selectedBusinessId] : [];
                }
            }
        }

    </script>


</x-app-layout>
