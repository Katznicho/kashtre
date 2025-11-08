<x-app-layout>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:flex md:items-center md:justify-between">
            <div>
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Refund Workflow Settings
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Configure the 3-step approval workflow (Service Point Supervisor → Authorizer → Approver) for refunds per business
            </p>
            </div>
            @php
                $permissions = auth()->user()->permissions ?? [];
                $canUseBulkLoader = in_array('Edit Credit Note Workflows', $permissions);
                $canAddWorkflow = in_array('Add Credit Note Workflows', $permissions);
            @endphp
            @if($canUseBulkLoader || $canAddWorkflow)
                <div class="mt-4 md:mt-0 flex items-center space-x-3">
                    @if($canUseBulkLoader)
                        <a href="{{ route('credit-note-workflows.bulk-upload.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Bulk Loader
                        </a>
                    @endif
                    @if($canAddWorkflow)
                        <a href="{{ route('credit-note-workflows.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Workflow
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Livewire Table -->
        <livewire:credit-note-workflows.list-credit-note-workflows />
    </div>
</div>
</x-app-layout>

