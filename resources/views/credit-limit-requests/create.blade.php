<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Credit Limit Change Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('credit-limit-requests.store') }}">
                        @csrf

                        <!-- Entity Info -->
                        @if($entity)
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Entity Information</h3>
                                <p class="text-sm text-gray-600">
                                    <strong>Name:</strong> {{ $entity->name ?? ($entity->payer_name ?? 'N/A') }}<br>
                                    <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $entityType)) }}
                                </p>
                            </div>
                        @endif

                        <input type="hidden" name="entity_type" value="{{ $entityType }}">
                        <input type="hidden" name="entity_id" value="{{ $entityId }}">
                        <input type="hidden" name="current_credit_limit" value="{{ $currentCreditLimit }}">

                        <!-- Current Credit Limit -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Credit Limit
                            </label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                <p class="text-lg font-semibold text-gray-900">
                                    UGX {{ number_format($currentCreditLimit, 2) }}
                                </p>
                            </div>
                        </div>

                        <!-- Requested Credit Limit -->
                        <div class="mb-4">
                            <label for="requested_credit_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                Requested Credit Limit <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="requested_credit_limit" 
                                   id="requested_credit_limit"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('requested_credit_limit') }}"
                                   placeholder="e.g. {{ number_format($currentCreditLimit * 1.5, 2) }} (increase) or {{ number_format($currentCreditLimit * 0.75, 2) }} (decrease)"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('requested_credit_limit') border-red-300 @enderror">
                            @error('requested_credit_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Enter the new credit limit. Can be higher (upgrade) or lower (downgrade) than current limit ({{ number_format($currentCreditLimit, 2) }})
                            </p>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Change
                            </label>
                            <textarea name="reason" 
                                      id="reason" 
                                      rows="4"
                                      placeholder="e.g. Client has demonstrated consistent payment history and requires increased credit limit for upcoming services..."
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason') border-red-300 @enderror">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Provide a brief explanation for the credit limit change request (upgrade or downgrade).
                            </p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="{{ route('credit-limit-requests.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-700">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

