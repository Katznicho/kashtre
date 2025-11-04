<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $contractorProfile->user->name ?? 'Contractor' }} - Withdrawal Requests
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('contractor-balance-statement.show', $contractorProfile) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Contractor Account Statement
                </a>
                <a href="{{ route('contractor-withdrawal-requests.create', $contractorProfile) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    New Withdrawal Request
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-transparent">
                <div class="p-6">
                    
                    <!-- Contractor Info -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Contractor Information</h3>
                        <p class="text-blue-700"><strong>{{ $contractorProfile->user->name ?? 'Contractor' }}</strong></p>
                        <p class="text-blue-600">Business: {{ $contractorProfile->business->name }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('contractor-withdrawal-requests.create', $contractorProfile) }}"
                           class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">
                            Create Withdrawal Request
                        </a>
                    </div>

                    <!-- Withdrawal Requests Table (Filament) -->
                    <div class="p-0">
                        <livewire:contractor-withdrawals.list-contractor-withdrawal-requests :contractorProfileId="$contractorProfile->id" />
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

