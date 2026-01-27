<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Third Party Vendor Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('settings.index', ['tab' => 'insurance-companies']) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company Code</dt>
                                    <dd class="mt-1">
                                        @if($insuranceCompany->code)
                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-mono font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $insuranceCompany->code }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->email ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->phone ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">TIN (Tax Identification Number)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->tin ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Website</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($insuranceCompany->website)
                                            <a href="{{ $insuranceCompany->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $insuranceCompany->website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->address ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Head Office Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->head_office_address ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Postal Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->postal_address ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Third-Party Registration Status -->
                        <div class="md:col-span-2 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Third-Party System Registration</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Status</dt>
                                    <dd class="mt-1">
                                        @if($insuranceCompany->third_party_business_id)
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Registered
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Not Registered
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                @if($insuranceCompany->third_party_business_id)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Third-Party Business ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->third_party_business_id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Third-Party User ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $insuranceCompany->third_party_user_id ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $insuranceCompany->third_party_username ?? 'N/A' }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Description -->
                        @if($insuranceCompany->description)
                        <div class="md:col-span-2 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                            <p class="text-sm text-gray-700">{{ $insuranceCompany->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
