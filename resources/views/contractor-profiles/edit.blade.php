@php
    use App\Models\Business;
    use App\Models\User;

    $businesses = Business::all();
    $users = User::all();
@endphp

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Contractor Profile</h2>
                    <a href="{{ route('contractor-profiles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('contractor-profiles.update', $contractorProfile->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Business Selection -->
                        <div>
                            <label for="business_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Business <span class="text-red-500">*</span>
                            </label>
                            <select name="business_id" id="business_id" required class="form-select w-full">
                                <option value="">Select Business</option>
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}" {{ old('business_id', $contractorProfile->business_id) == $business->id ? 'selected' : '' }}>
                                        {{ $business->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User Selection -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                User <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required class="form-select w-full">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $contractorProfile->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bank Name -->
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="bank_name" id="bank_name" required 
                                   value="{{ old('bank_name', $contractorProfile->bank_name) }}"
                                   class="form-input w-full" placeholder="Enter bank name">
                        </div>

                        <!-- Account Name -->
                        <div>
                            <label for="account_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="account_name" id="account_name" required 
                                   value="{{ old('account_name', $contractorProfile->account_name) }}"
                                   class="form-input w-full" placeholder="Enter account name">
                        </div>

                        <!-- Account Number -->
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="account_number" id="account_number" required 
                                   value="{{ old('account_number', $contractorProfile->account_number) }}"
                                   class="form-input w-full" placeholder="Enter account number">
                        </div>

                        <!-- Account Balance -->
                        <div>
                            <label for="account_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Balance <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="account_balance" id="account_balance" required 
                                   value="{{ old('account_balance', $contractorProfile->account_balance) }}"
                                   class="form-input w-full" placeholder="Enter account balance" step="0.01" min="0">
                        </div>

                        <!-- Kashtre Account Number -->
                        <div>
                            <label for="kashtre_account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Kashtre Account Number
                            </label>
                            <input type="text" name="kashtre_account_number" id="kashtre_account_number" 
                                   value="{{ old('kashtre_account_number', $contractorProfile->kashtre_account_number) }}"
                                   class="form-input w-full" 
                                   placeholder="KC + 10 random characters (e.g., KC1234567890)">
                            <p class="mt-1 text-sm text-gray-500">
                                Format: KC + 10 random characters. Leave empty to auto-generate.
                            </p>
                        </div>

                        <!-- Signing Qualifications -->
                        <div>
                            <label for="signing_qualifications" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Signing Qualifications
                            </label>
                            <input type="text" name="signing_qualifications" id="signing_qualifications" 
                                   value="{{ old('signing_qualifications', $contractorProfile->signing_qualifications) }}"
                                   class="form-input w-full" placeholder="Enter signing qualifications">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('contractor-profiles.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Contractor Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 