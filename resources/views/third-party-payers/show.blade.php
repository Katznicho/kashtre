<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Third Party Payer Details') }} - {{ $thirdPartyPayer->name }}
            </h2>
            <div class="flex space-x-2">
                @php
                    $isInitiator = \App\Models\CreditLimitApprovalApprover::where('business_id', auth()->user()->business_id)
                        ->where('approver_id', auth()->user()->id)
                        ->where('approval_level', 'initiator')
                        ->exists();
                @endphp
                @if($thirdPartyPayer->credit_limit !== null && $thirdPartyPayer->credit_limit >= 0 && in_array('Manage Credit Limits', (array) (auth()->user()->permissions ?? [])) && $isInitiator)
                    <a href="{{ route('credit-limit-requests.create', ['entity_type' => 'third_party_payer', 'entity_id' => $thirdPartyPayer->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Request Credit Limit Change
                    </a>
                @endif
                <a href="{{ route('third-party-payers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Third Party Payer Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payer Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($thirdPartyPayer->type === 'insurance_company') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $thirdPartyPayer->type)) }}
                                        </span>
                                    </dd>
                                </div>
                                @if($thirdPartyPayer->type === 'insurance_company' && $thirdPartyPayer->insuranceCompany)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Insurance Company</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->insuranceCompany->name }}</dd>
                                    </div>
                                @endif
                                @if($thirdPartyPayer->type === 'normal_client' && $thirdPartyPayer->client)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Client</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->client->name }}</dd>
                                    </div>
                                @endif
                                @if($thirdPartyPayer->contact_person)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->contact_person }}</dd>
                                    </div>
                                @endif
                                @if($thirdPartyPayer->phone_number)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->phone_number }}</dd>
                                    </div>
                                @endif
                                @if($thirdPartyPayer->email)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->email }}</dd>
                                    </div>
                                @endif
                                @if($thirdPartyPayer->address)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->address }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Credit Limit</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        UGX {{ number_format($thirdPartyPayer->credit_limit ?? 0, 2) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($thirdPartyPayer->status === 'active') bg-green-100 text-green-800
                                            @elseif($thirdPartyPayer->status === 'suspended') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($thirdPartyPayer->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Business</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->business->name ?? 'N/A' }}</dd>
                                </div>
                                @if($thirdPartyPayer->notes)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $thirdPartyPayer->notes }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit Limit Change Requests -->
            @if($thirdPartyPayer->credit_limit !== null)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Credit Limit Change Requests</h3>
                        @php
                            $creditRequests = $thirdPartyPayer->creditLimitChangeRequests()
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        @if($creditRequests->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested Limit</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($creditRequests as $request)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    UGX {{ number_format($request->requested_credit_limit, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                        @if($request->status === 'approved') bg-green-100 text-green-800
                                                        @elseif($request->status === 'rejected') bg-red-100 text-red-800
                                                        @elseif($request->status === 'authorized') bg-blue-100 text-blue-800
                                                        @elseif($request->status === 'initiated') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($request->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $request->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('credit-limit-requests.show', $request) }}" class="text-blue-600 hover:text-blue-900">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('credit-limit-requests.index', ['entity_type' => 'third_party_payer', 'entity_id' => $thirdPartyPayer->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View All Requests →
                                </a>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No credit limit change requests found.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

