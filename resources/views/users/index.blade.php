@php
    use App\Models\Business;
    use App\Models\Branch;
    
    $businesses = Business::with('branches')->where('id', '!=', 1)->get()->keyBy('id');
    $userBusinessId = Auth::user()->business_id;
@endphp

<x-app-layout>
    <div class="py-12" x-data="userForm()" x-init="init()" @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Staff</h2>

                    <div class="flex items-center space-x-3">
                        @if(in_array('Add Staff', (array) $permissions))
                        <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                            ➕ Create Staff
                        </a>
                        @endif

                        @if(in_array('Add Staff', (array) $permissions))
                        <button @click="showTemplateModal = true" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition duration-150">
                            📥 Download Template
                        </button>
                        @endif

                        @if(in_array('Add Staff', (array) $permissions))
                        <button @click="showBulkUploadModal = true" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-semibold rounded-md hover:bg-orange-700 transition duration-150">
                            📤 Bulk Upload
                        </button>
                        @endif
                    </div>
                </div>

                @livewire('list-users')
            </div>
        </div>

        <!-- Template Download Modal -->
        <div x-show="showTemplateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Download Staff Template</h3>
                    
                    <form action="{{ route('users.bulk.template') }}" method="GET" class="space-y-4" x-data="{ downloading: false }" @submit="downloading = true">
                        <!-- Business Selection (only for admin users) -->
                        <div x-show="userBusinessId === 1">
                            <label for="template_business_id" class="block text-sm font-medium text-gray-700 mb-1">Select Business</label>
                            <select name="business_id" id="template_business_id" required class="form-select w-full" x-model="selectedBusinessId" @change="updateBranches">
                                <option value="" disabled>Select Business</option>
                                @foreach($businesses as $id => $business)
                                    <option value="{{ $id }}">{{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch Selection -->
                        <div>
                            <label for="template_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Select Branch</label>
                            <select name="branch_id" id="template_branch_id" required class="form-select w-full" :disabled="!branches.length">
                                <option value="" disabled>Select Branch</option>
                                <template x-for="branch in branches" :key="branch.id">
                                    <option :value="branch.id" x-text="branch.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" @click="showTemplateModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400" :disabled="downloading">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="downloading">
                                <span x-show="!downloading">Download Template</span>
                                <span x-show="downloading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Downloading...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Modal -->
        <div x-show="showBulkUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Upload Staff</h3>
                    
                    <form action="{{ route('users.bulk.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ uploading: false }" @submit="uploading = true">
                        @csrf
                        
                        <!-- Business Selection (only for admin users) -->
                        <div x-show="userBusinessId === 1">
                            <label for="upload_business_id" class="block text-sm font-medium text-gray-700 mb-1">Select Business</label>
                            <select name="business_id" id="upload_business_id" required class="form-select w-full" x-model="uploadBusinessId" @change="updateUploadBranches">
                                <option value="" disabled>Select Business</option>
                                @foreach($businesses as $id => $business)
                                    <option value="{{ $id }}">{{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch Selection -->
                        <div>
                            <label for="upload_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Select Branch</label>
                            <select name="branch_id" id="upload_branch_id" required class="form-select w-full" :disabled="!uploadBranches.length">
                                <option value="" disabled>Select Branch</option>
                                <template x-for="branch in uploadBranches" :key="branch.id">
                                    <option :value="branch.id" x-text="branch.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label for="template" class="block text-sm font-medium text-gray-700 mb-1">Upload Staff Template</label>
                            <input type="file" name="template" accept=".xlsx,.xls" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <p class="text-xs text-gray-500">Upload an Excel file (.xlsx or .xls) with staff data. Download the template first to see the required format.</p>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" @click="showBulkUploadModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400" :disabled="uploading">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="uploading">
                                <span x-show="!uploading">Upload Template</span>
                                <span x-show="uploading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function userForm() {
            return {
                showTemplateModal: false,
                showBulkUploadModal: false,
                selectedBusinessId: '',
                uploadBusinessId: '',
                userBusinessId: {{ $userBusinessId }},
                branches: [],
                uploadBranches: [],
                businessData: @json($businesses->map(function($b) {
                    return [
                        'id' => $b->id,
                        'branches' => $b->branches->map(function($br) {
                            return ['id' => $br->id, 'name' => $br->name];
                        })->values()->all()
                    ];
                })->values()->all()),
                
                init() {
                    // If user is not from business 1, force their business ID
                    if (this.userBusinessId !== 1) {
                        this.selectedBusinessId = this.userBusinessId.toString();
                        this.uploadBusinessId = this.userBusinessId.toString();
                    }
                    this.updateBranches();
                    this.updateUploadBranches();
                },
                
                updateBranches() {
                    const biz = this.businessData.find(b => b.id == this.selectedBusinessId);
                    this.branches = biz ? biz.branches : [];
                },
                
                updateUploadBranches() {
                    const biz = this.businessData.find(b => b.id == this.uploadBusinessId);
                    this.uploadBranches = biz ? biz.branches : [];
                }
            }
        }
    </script>
</x-app-layout>
