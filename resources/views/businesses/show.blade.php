<x-app-layout>
    <div class="py-12 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Business Details</h2>
                <a href="{{ route('businesses.edit', $business) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Edit</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logo -->
                <div class="md:col-span-2 flex justify-center mb-4">
                    @if ($business->logo)
                        <img src="{{ asset('storage/' . $business->logo) }}" alt="{{ $business->name }} Logo" 
                             class="h-32 w-32 rounded-full object-cover">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-500 dark:text-gray-400">No Logo</span>
                        </div>
                    @endif
                </div>

                <!-- Name -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Name:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $business->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Email:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $business->email }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Phone:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $business->phone }}</p>
                </div>

                <!-- Address -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Address:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $business->address }}</p>
                </div>

                <!-- Account Number -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Account Number:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $business->account_number }}</p>
                </div>

                 <!-- Status -->
                 @if (isset($business->status))
                 <div>
                    <strong class="text-gray-700 dark:text-gray-300">Status:</strong>
                    <p class="text-gray-900 dark:text-white">{{ ucfirst($business->status) }}</p>
                </div>
                 @endif
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('businesses.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
