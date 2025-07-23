<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Admin Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Surname</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->surname }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">First Name</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->first_name }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Middle Name</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->middle_name ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Email</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->email }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Phone</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->phone }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">NIN</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $admin->nin }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Gender</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ ucfirst($admin->gender) }}</p>
                    </div>
                    <div>
                        <label class="text-gray-600 dark:text-gray-300 font-semibold">Status</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ ucfirst($admin->status) }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="text-gray-600 dark:text-gray-300 font-semibold">Profile Photo</label>
                    <div class="mt-2">
                        @if($admin->profile_photo_path)
                            <img src="{{ asset('storage/' . $admin->profile_photo_path) }}" class="w-24 h-24 rounded-full shadow" alt="Profile Photo">
                        @else
                            <p class="text-gray-500">No profile photo uploaded</p>
                        @endif
                    </div>
                </div>

                <div class="mb-6">
                    <label class="text-gray-600 dark:text-gray-300 font-semibold">Permissions</label>
                    <div class="mt-2">
                        @if(!empty($admin->permissions) && is_array($admin->permissions))
                            <ul class="list-disc list-inside text-gray-900 dark:text-white space-y-1">
                                @foreach($admin->permissions as $permission)
                                    <li>{{ $permission }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">No permissions assigned.</p>
                        @endif
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('admins.edit', $admin->id) }}" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Edit</a>
                    <a href="{{ route('admins.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
