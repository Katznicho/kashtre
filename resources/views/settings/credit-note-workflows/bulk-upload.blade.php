<x-app-layout>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('credit-note-workflows.index') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                    </svg>
                                    <span class="sr-only">Refund Workflows</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500">Bulk Loader</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Refund Workflow Bulk Loader
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Select a business, download the Excel template, fill in workflow approvers and service point supervisors, then upload it to create or update the workflow in one step.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('import_summary') && !empty(session('import_summary')['errors']))
            <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative">
                <p class="font-medium">Import Warnings</p>
                <ul class="mt-2 space-y-1 text-sm list-disc list-inside">
                    @foreach(session('import_summary')['errors'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="{{ route('credit-note-workflows.bulk-upload.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label for="business_id" class="block text-sm font-medium text-gray-700">Business</label>
                        <select name="business_id" id="business_id" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select a business...</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" @selected(optional($selectedBusiness)->id === $business->id)>
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                            Load Business
                        </button>
                    </div>
                </form>
                @if($selectedBusiness)
                    <div class="mt-4 border border-blue-100 bg-blue-50 rounded-md p-4">
                        <h3 class="text-sm font-semibold text-blue-900 flex items-center">
                            <svg class="h-4 w-4 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10l9 4 9-4V7"></path>
                            </svg>
                            {{ $selectedBusiness->name }}
                        </h3>
                        <p class="mt-1 text-sm text-blue-800">
                            @if($existingWorkflow)
                                Existing workflow found — default supervisor: <span class="font-medium">{{ optional($existingWorkflow->defaultSupervisor)->name ?? 'Not set' }}</span>, authorizer: <span class="font-medium">{{ optional($existingWorkflow->finance)->name ?? 'Not set' }}</span>, approver: <span class="font-medium">{{ optional($existingWorkflow->ceo)->name ?? 'Not set' }}</span>. Status: <span class="font-medium">{{ $existingWorkflow->is_active ? 'Active' : 'Inactive' }}</span>.
                            @else
                                No workflow exists yet. The uploaded template will create one for this business.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if($selectedBusiness)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">1. Generate Template</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Pick the supervisors you want included as columns. In the spreadsheet, mark each role or service point by typing <span class="font-semibold text-gray-700">'Y'</span> under the staff member who should handle it.
                        </p>

                        <form action="{{ route('credit-note-workflows.bulk-upload.template') }}" method="GET" class="mt-4 space-y-4">
                            <input type="hidden" name="business_id" value="{{ $selectedBusiness->id }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search Supervisors</label>
                                <input type="text" id="supervisor-search" placeholder="Search by name or email..." class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <fieldset class="border border-gray-200 rounded-md p-3 max-h-56 overflow-y-auto" id="supervisor-checkboxes">
                                <legend class="text-xs uppercase text-gray-500 tracking-wide px-1">Available Supervisors</legend>
                        @forelse($businessUsers as $user)
                                <label class="flex items-start space-x-2 py-1 supervisor-checkbox" data-search-text="{{ strtolower($user->name . ' ' . $user->email) }}">
                                    <input type="checkbox" name="supervisor_ids[]" value="{{ $user->id }}" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <span class="text-sm text-gray-700">
                                        {{ $user->name }}
                                        <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                    </span>
                                </label>
                        @empty
                                <p class="text-sm text-gray-500">No active supervisors available for this business.</p>
                        @endforelse
                            </fieldset>

                            <div class="rounded-md bg-blue-50 p-3 text-xs text-blue-800">
                                <p><strong>Template Tips:</strong></p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>The first rows after the header are <em>Approver</em>, a blank spacer, <em>Authorizer</em>, another spacer, then a <em>Service Points</em> title.</li>
                                    <li>Type <strong>Y</strong> beneath each staff member assigned to that role. Approvers and Authorizers must have between 1 and 3 members.</li>
                                    <li>Each service point row can have between 1 and 4 supervisors marked with <strong>Y</strong>.</li>
                                </ul>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" @if($businessUsers->isEmpty()) disabled @endif>
                                    Download Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">2. Import Completed Template</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Upload the updated spreadsheet after marking each role/service point with <strong>Y</strong>. We will create or update the workflow, set approvers, and assign supervisors based on the entries.
                        </p>

                        <form action="{{ route('credit-note-workflows.bulk-upload.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="business_id" value="{{ $selectedBusiness->id }}">
                            <div>
                                <label for="bulk_file" class="block text-sm font-medium text-gray-700">Select File</label>
                                <input type="file" name="file" id="bulk_file" accept=".xlsx,.xls" required class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-2 text-xs text-gray-500">Supported formats: .xlsx, .xls (max 10MB).</p>
                                @error('file')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Upload &amp; Apply
                                </button>
                            </div>
                        </form>

                        @if(session('import_summary'))
                            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-md p-4">
                                <h4 class="text-sm font-semibold text-gray-700">Last Import Summary</h4>
                                <dl class="mt-2 text-sm text-gray-600 space-y-1">
                                    <div class="flex justify-between">
                                        <dt>Updated Assignments</dt>
                                        <dd>{{ session('import_summary')['updated'] ?? 0 }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Unchanged Service Points</dt>
                                        <dd>{{ session('import_summary')['unchanged'] ?? 0 }}</dd>
                                    </div>
                                    @if(!empty(session('import_summary')['workflow_status']))
                                        <div class="flex justify-between">
                                            <dt>Workflow Status</dt>
                                            <dd>{{ session('import_summary')['workflow_status'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Service Points Overview</h3>
                    <p class="mt-1 text-sm text-gray-500">These service points are included in the template for {{ $selectedBusiness->name }}.</p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Point</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($servicePoints as $servicePoint)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $servicePoint->id }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $servicePoint->name ?? 'Unnamed Service Point' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $servicePoint->description ?? '—' }}</td>
                                </tr>
                        @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm text-gray-500 text-center">No service points available.</td>
                                </tr>
                        @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <p class="text-sm text-gray-600">To get started, choose a business above. The bulk loader will prepare a template tailored to that entity.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('supervisor-search');
    const checkboxRows = document.querySelectorAll('#supervisor-checkboxes .supervisor-checkbox');

    if (searchInput && checkboxRows.length > 0) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            checkboxRows.forEach(row => {
                const searchText = row.getAttribute('data-search-text') || '';
                row.style.display = searchText.includes(query) ? 'flex' : 'none';
            });
        });
    }
});
</script>
</x-app-layout>

