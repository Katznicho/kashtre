<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('clients.edit', $client) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Client
                </a>
                <a href="{{ route('clients.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Client ID and Status -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Client Information</h3>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500">Client ID: {{ $client->client_id }}</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $client->status === 'active' ? 'bg-green-100 text-green-800' : 
                                       ($client->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Personal Information</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="text-sm text-gray-900">{{ $client->name }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Surname</label>
                                        <p class="text-sm text-gray-900">{{ $client->surname }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">First Name</label>
                                        <p class="text-sm text-gray-900">{{ $client->first_name }}</p>
                                    </div>
                                </div>
                                @if($client->other_names)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Other Names</label>
                                    <p class="text-sm text-gray-900">{{ $client->other_names }}</p>
                                </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Sex</label>
                                        <p class="text-sm text-gray-900">{{ ucfirst($client->sex) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                                        <p class="text-sm text-gray-900">{{ $client->date_of_birth ? \Carbon\Carbon::parse($client->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Marital Status</label>
                                        <p class="text-sm text-gray-900">{{ $client->marital_status ? ucfirst($client->marital_status) : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Occupation</label>
                                        <p class="text-sm text-gray-900">{{ $client->occupation ?: 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Contact Information</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Phone Number</label>
                                    <p class="text-sm text-gray-900">{{ $client->phone_number }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email</label>
                                    <p class="text-sm text-gray-900">{{ $client->email ?: 'N/A' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">County</label>
                                        <p class="text-sm text-gray-900">{{ $client->county ?: 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Village</label>
                                        <p class="text-sm text-gray-900">{{ $client->village ?: 'N/A' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Payment Methods</label>
                                    <div class="text-sm text-gray-900">
                                        @if($client->payment_methods && count($client->payment_methods) > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($client->payment_methods as $method)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        @switch($method)
                                                            @case('packages')
                                                                📦 Packages
                                                                @break
                                                            @case('insurance')
                                                                🛡️ Insurance
                                                                @break
                                                            @case('credit_arrangement')
                                                                💳 Credit Arrangement
                                                                @break
                                                            @case('deposits')
                                                                💰 Deposits/A/c Balance
                                                                @break
                                                            @case('mobile_money')
                                                                📱 MM (Mobile Money)
                                                                @break
                                                            @case('v_card')
                                                                💳 V Card (Virtual Card)
                                                                @break
                                                            @case('p_card')
                                                                💳 P Card (Physical Card)
                                                                @break
                                                            @case('bank_transfer')
                                                                🏦 Bank Transfer
                                                                @break
                                                            @case('cash')
                                                                💵 Cash
                                                                @break
                                                            @default
                                                                {{ ucwords(str_replace('_', ' ', $method)) }}
                                                        @endswitch
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500">No payment methods selected</span>
                                        @endif
                                    </div>
                                </div>
                                @if($client->payment_phone_number)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Payment Phone Number</label>
                                    <p class="text-sm text-gray-900">{{ $client->payment_phone_number }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Identification -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Identification</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">NIN</label>
                                <p class="text-sm text-gray-900">{{ $client->nin ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">TIN Number</label>
                                <p class="text-sm text-gray-900">{{ $client->tin_number ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Services and Visit Information -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Services & Visit Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Services Category</label>
                                <p class="text-sm text-gray-900">
                                    @if($client->services_category)
                                        {{ ucwords(str_replace('_', ' ', $client->services_category)) }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Visit ID</label>
                                <p class="text-sm text-gray-900">{{ $client->visit_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Next of Kin Information -->
                    @if($client->nok_surname || $client->nok_first_name)
                    <div class="bg-gray-50 p-4 rounded-lg mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Next of Kin Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Surname</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_surname ?: 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">First Name</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_first_name ?: 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($client->nok_other_names)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Other Names</label>
                                    <p class="text-sm text-gray-900">{{ $client->nok_other_names }}</p>
                                </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Marital Status</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_marital_status ? ucfirst($client->nok_marital_status) : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Occupation</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_occupation ?: 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Gender</label>
                                    <p class="text-sm text-gray-900">{{ $client->nok_sex ? ucfirst($client->nok_sex) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Phone Number</label>
                                    <p class="text-sm text-gray-900">{{ $client->nok_phone_number ?: 'N/A' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">County</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_county ?: 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Village</label>
                                        <p class="text-sm text-gray-900">{{ $client->nok_village ?: 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Financial Information -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Financial Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Current Balance</label>
                                <p class="text-sm text-gray-900">UGX {{ number_format($client->balance ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Created Date</label>
                                <p class="text-sm text-gray-900">{{ $client->created_at ? $client->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('clients.edit', $client) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Client
                        </a>
                        <a href="{{ route('clients.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
