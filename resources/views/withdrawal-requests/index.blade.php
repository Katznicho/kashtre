<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Withdrawal Requests') }}
        </h2>
            <a href="{{ route('business-balance-statement.index') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Back to Balance Statement
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-100 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-800">Pending</p>
                                    <p class="text-2xl font-bold text-yellow-900">{{ $withdrawalRequests->where('status', 'pending')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800">Approved</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ $withdrawalRequests->whereIn('status', ['business_approved', 'kashtre_approved', 'approved'])->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">Completed</p>
                                    <p class="text-2xl font-bold text-green-900">{{ $withdrawalRequests->where('status', 'completed')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-red-100 rounded-full">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">Rejected</p>
                                    <p class="text-2xl font-bold text-red-900">{{ $withdrawalRequests->where('status', 'rejected')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Withdrawal Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($withdrawalRequests as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ substr($request->uuid, 0, 8) }}...
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->business->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="text-right">
                                                <div class="font-semibold">{{ number_format($request->amount, 2) }} UGX</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($request->request_type === 'charge') bg-red-100 text-red-800
                                                @else bg-green-100 text-green-800 @endif">
                                                @if($request->request_type === 'charge')
                                                    Charge
                                                @else
                                                    Withdrawal
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($request->withdrawal_type === 'express') bg-orange-100 text-orange-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($request->withdrawal_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($request->status === 'business_approved') bg-blue-100 text-blue-800
                                                @elseif($request->status === 'kashtre_approved') bg-indigo-100 text-indigo-800
                                                @elseif($request->status === 'approved') bg-green-100 text-green-800
                                                @elseif($request->status === 'rejected') bg-red-100 text-red-800
                                                @elseif($request->status === 'processing') bg-purple-100 text-purple-800
                                                @elseif($request->status === 'completed') bg-green-100 text-green-800
                                                @elseif($request->status === 'failed') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $request->formatted_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->requester->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('withdrawal-requests.show', $request) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No withdrawal requests found</h3>
                                                <p class="text-gray-500">You haven't created any withdrawal requests yet.</p>
                                                @if(auth()->user()->business_id != 1)
                                                    <a href="{{ route('withdrawal-requests.create') }}" 
                                                       class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                                        Create Withdrawal Request
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($withdrawalRequests->hasPages())
                        <div class="mt-6">
                            {{ $withdrawalRequests->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

</x-app-layout>