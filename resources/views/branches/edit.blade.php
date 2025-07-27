<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Branch</h2>
                    <a href="{{ route('branches.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded-md hover:bg-gray-600 transition duration-150">
                        ← Back to Branches
                    </a>
                </div>

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show"
                        class="relative bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 transition"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button @click="show = false"
                            class="absolute top-1 right-2 text-xl font-semibold text-green-700">
                            &times;
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show"
                        class="relative bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 transition"
                        role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button @click="show = false"
                            class="absolute top-1 right-2 text-xl font-semibold text-red-700">
                            &times;
                        </button>
                    </div>
                @endif

                <form action="{{ route('branches.update', $branch) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Business Information (Read-only) -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-3">Business Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Name</label>
                                <input type="text" value="{{ $branch->business->name }}" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100 text-gray-600 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Email</label>
                                <input type="text" value="{{ $branch->business->email }}" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100 text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Branch Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Branch Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                value="{{ old('name', $branch->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-[#011478] focus:ring focus:ring-[#011478]/20">
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Branch Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                value="{{ old('email', $branch->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-[#011478] focus:ring focus:ring-[#011478]/20">
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" id="phone" required
                                value="{{ old('phone', $branch->phone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-[#011478] focus:ring focus:ring-[#011478]/20">
                            @error('phone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" required
                                value="{{ old('address', $branch->address) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-[#011478] focus:ring focus:ring-[#011478]/20">
                            @error('address')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Branch Information (Read-only) -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-3">Branch Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch UUID</label>
                                <input type="text" value="{{ $branch->uuid }}" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100 text-gray-600 cursor-not-allowed text-xs">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created Date</label>
                                <input type="text" value="{{ $branch->created_at->format('M d, Y - H:i A') }}" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100 text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <!-- Branch Stats -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-3">Branch Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $branch->users->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Users</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $branch->users->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Users This Month</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $branch->updated_at->diffForHumans() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Last Updated</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('branches.index') }}" 
                           class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90 transition duration-150">
                            Update Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
