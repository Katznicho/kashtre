<x-app-layout>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('bank-schedules.index') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                    </svg>
                                    <span class="sr-only">Bank Schedules</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500">Create</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create Bank Schedule
                </h2>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('bank-schedules.store') }}" method="POST" class="px-4 py-5 sm:p-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Business Selection -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700">
                                Business <span class="text-red-500">*</span>
                            </label>
                            <select name="business_id" id="business_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('business_id') border-red-300 @enderror">
                                <option value="">Select a business...</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('business_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Client Name -->
                        <div>
                            <label for="client_name" class="block text-sm font-medium text-gray-700">
                                Client Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="client_name" id="client_name" required
                                   value="{{ old('client_name') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('client_name') border-red-300 @enderror"
                                   placeholder="Enter client name">
                            @error('client_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount" id="amount" required step="0.01" min="0.01"
                                   value="{{ old('amount') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('amount') border-red-300 @enderror"
                                   placeholder="0.00">
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Withdrawal Charge -->
                        <div>
                            <label for="withdrawal_charge" class="block text-sm font-medium text-gray-700">
                                Withdrawal Charge
                            </label>
                            <input type="number" name="withdrawal_charge" id="withdrawal_charge" step="0.01" min="0"
                                   value="{{ old('withdrawal_charge', 0) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('withdrawal_charge') border-red-300 @enderror"
                                   placeholder="0.00">
                            @error('withdrawal_charge')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bank Name -->
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="bank_name" id="bank_name" required
                                   value="{{ old('bank_name') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('bank_name') border-red-300 @enderror"
                                   placeholder="Enter bank name">
                            @error('bank_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bank Account -->
                        <div>
                            <label for="bank_account" class="block text-sm font-medium text-gray-700">
                                Bank Account <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="bank_account" id="bank_account" required
                                   value="{{ old('bank_account') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('bank_account') border-red-300 @enderror"
                                   placeholder="Enter bank account number">
                            @error('bank_account')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Withdrawal Request (Optional) -->
                        <div>
                            <label for="withdrawal_request_id" class="block text-sm font-medium text-gray-700">
                                Withdrawal Request (Optional)
                            </label>
                            <select name="withdrawal_request_id" id="withdrawal_request_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('withdrawal_request_id') border-red-300 @enderror">
                                <option value="">None</option>
                                @foreach($withdrawalRequests as $request)
                                    <option value="{{ $request->id }}" {{ old('withdrawal_request_id') == $request->id ? 'selected' : '' }}>
                                        {{ $request->uuid }} - {{ $request->business->name }} - {{ number_format($request->amount, 2) }} UGX
                                    </option>
                                @endforeach
                            </select>
                            @error('withdrawal_request_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reference ID (Optional) -->
                        <div>
                            <label for="reference_id" class="block text-sm font-medium text-gray-700">
                                Reference ID (Optional)
                            </label>
                            <input type="text" name="reference_id" id="reference_id"
                                   value="{{ old('reference_id') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('reference_id') border-red-300 @enderror"
                                   placeholder="Enter reference ID">
                            @error('reference_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-300 @enderror">
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processed" {{ old('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('bank-schedules.index') }}" 
                               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Bank Schedule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>


