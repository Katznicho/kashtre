<x-app-layout>
    <div class="py-12 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Branch Details</h2>
                <a href="{{ route('branches.edit', $branch) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Edit</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Business Name -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Business:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->business->name }}</p>
                </div>

                <!-- Branch Name -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Branch Name:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Email:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->email }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Phone:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->phone }}</p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <strong class="text-gray-700 dark:text-gray-300">Address:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->address }}</p>
                </div>

                <!-- UUID -->
                <div class="md:col-span-2">
                    <strong class="text-gray-700 dark:text-gray-300">UUID:</strong>
                    <p class="text-gray-900 dark:text-white font-mono text-sm">{{ $branch->uuid }}</p>
                </div>

                <!-- Created At -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Created:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->created_at->format('M d, Y g:i A') }}</p>
                </div>

                <!-- Updated At -->
                <div>
                    <strong class="text-gray-700 dark:text-gray-300">Last Updated:</strong>
                    <p class="text-gray-900 dark:text-white">{{ $branch->updated_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('branches.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
