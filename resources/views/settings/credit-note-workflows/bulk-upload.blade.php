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
                                <a href="{{ route('credit-note-workflows.show', $workflow) }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Refund Workflow</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500">Bulk Assign Supervisors</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Bulk Assign Supervisors
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Configure service point supervisors for {{ $workflow->business->name }} using a spreadsheet template. Select the staff you want to include, download the template, make your updates, and import the file to update assignments in bulk.
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('credit-note-workflows.show', $workflow) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Back to Workflow
                </a>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">1. Generate Template</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose the supervisors you want to appear as columns. Only the selected staff members will be included in the spreadsheet for quick ticking.</p>

                    <form action="{{ route('credit-note-workflows.bulk-upload.template', $workflow) }}" method="GET" class="mt-4 space-y-4" id="template-download-form">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search Supervisors</label>
                            <input type="text" id="supervisor-search" placeholder="Search by name or email..." class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <fieldset class="border border-gray-200 rounded-md p-3 max-h-56 overflow-y-auto" id="supervisor-checkboxes">
                            <legend class="text-xs uppercase text-gray-500 tracking-wide px-1">Available Supervisors</legend>
                            @foreach($businessUsers as $user)
                                <label class="flex items-start space-x-2 py-1 supervisor-checkbox" data-search-text="{{ strtolower($user->name . ' ' . $user->email) }}">
                                    <input type="checkbox" name="supervisor_ids[]" value="{{ $user->id }}" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <span class="text-sm text-gray-700">
                                        {{ $user->name }}
                                        <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                    </span>
                                </label>
                            @endforeach
                            @if($businessUsers->isEmpty())
                                <p class="text-sm text-gray-500">No active supervisors available for this business.</p>
                            @endif
                        </fieldset>

                        <p class="text-xs text-gray-500">If you do not select anyone, the template will include every active supervisor for this business.</p>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Download Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">2. Import Completed Template</h3>
                    <p class="mt-1 text-sm text-gray-500">Upload the updated spreadsheet to apply supervisor assignments. The system will update each service point based on the column you ticked.</p>

                    <form action="{{ route('credit-note-workflows.bulk-upload.import', $workflow) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
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
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Service Points Overview</h3>
                <p class="mt-1 text-sm text-gray-500">These are the service points that will be included in the template.</p>

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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('supervisor-search');
    const checkboxRows = document.querySelectorAll('#supervisor-checkboxes .supervisor-checkbox');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            checkboxRows.forEach(row => {
                const searchText = row.getAttribute('data-search-text') || '';
                const matches = searchText.includes(query);
                row.style.display = matches ? 'flex' : 'none';
            });
        });
    }
});
</script>
</x-app-layout>


