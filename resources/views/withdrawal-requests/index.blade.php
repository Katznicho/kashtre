<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Withdrawal Requests') }}
        </h2>
            <div class="flex gap-2">
                <a href="{{ route('withdrawal-requests.create') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Create Withdrawal Request
                </a>
                <a href="{{ route('business-balance-statement.index') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Back to Balance Statement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <!-- Summary Cards -->
                @php
                    $pendingCount = \App\Models\WithdrawalRequest::where('status', 'pending')->count();
                    $approvedCount = \App\Models\WithdrawalRequest::whereIn('status', ['business_approved','kashtre_approved','approved'])->count();
                    $completedCount = \App\Models\WithdrawalRequest::where('status', 'completed')->count();
                    $rejectedCount = \App\Models\WithdrawalRequest::where('status', 'rejected')->count();
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-sm text-yellow-800">Pending</div>
                        <div class="text-2xl font-bold text-yellow-900">{{ $pendingCount }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm text-blue-800">Approved (in progress)</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $approvedCount }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm text-green-800">Completed</div>
                        <div class="text-2xl font-bold text-green-900">{{ $completedCount }}</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-sm text-red-800">Rejected</div>
                        <div class="text-2xl font-bold text-red-900">{{ $rejectedCount }}</div>
                    </div>
                </div>
            </div>
            <div class="p-0">
                <livewire:withdrawal-requests.list-withdrawal-requests />
            </div>
        </div>
    </div>

</x-app-layout>