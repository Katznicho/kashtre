<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Accounts Receivable') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="container mx-auto px-4 py-8">
                        <div class="max-w-7xl mx-auto">
                            <!-- Header -->
                            <div class="mb-8">
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">Accounts Receivable</h1>
                                <p class="text-gray-600">
                                    @if(auth()->user()->business_id == 1)
                                        All Businesses - Pending Payments Overview
                                    @else
                                        Pending Payments from Clients
                                    @endif
                                </p>
                            </div>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                                <!-- Total Outstanding -->
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <div class="flex items-start">
                                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-500">Total Outstanding</h3>
                                            <p class="text-2xl font-bold text-blue-600">
                                                UGX {{ number_format($totalOutstanding ?? 0, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Total amount owed by all clients that hasn't been paid yet
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current -->
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <div class="flex items-start">
                                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-500">Current</h3>
                                            <p class="text-2xl font-bold text-green-600">
                                                UGX {{ number_format($totalCurrent ?? 0, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Amounts that are not yet due (within payment terms)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overdue -->
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <div class="flex items-start">
                                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-500">Overdue</h3>
                                            <p class="text-2xl font-bold text-red-600">
                                                UGX {{ number_format($totalOverdue ?? 0, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Amounts past their due date that need immediate attention
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Partial -->
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <div class="flex items-start">
                                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-500">Partial</h3>
                                            <p class="text-2xl font-bold text-yellow-600">
                                                UGX {{ number_format(($totalOutstanding ?? 0) - ($totalCurrent ?? 0) - ($totalOverdue ?? 0), 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Amounts where clients have made partial payments
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aging Summary -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-8">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-900">Aging Summary</h2>
                                    <p class="text-sm text-gray-500 mt-1">Breakdown of outstanding amounts by how long they've been unpaid</p>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                                            <p class="text-sm font-medium text-gray-700">Current (0-30 days)</p>
                                            <p class="text-xl font-bold text-green-600 mt-1">UGX {{ number_format($agingCurrent ?? 0, 2) }}</p>
                                            <p class="text-xs text-gray-500 mt-2">Recently issued, not yet due</p>
                                        </div>
                                        <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                            <p class="text-sm font-medium text-gray-700">31-60 days</p>
                                            <p class="text-xl font-bold text-yellow-600 mt-1">UGX {{ number_format($aging30_60 ?? 0, 2) }}</p>
                                            <p class="text-xs text-gray-500 mt-2">Moderately overdue, follow up needed</p>
                                        </div>
                                        <div class="text-center p-4 bg-orange-50 rounded-lg border border-orange-200">
                                            <p class="text-sm font-medium text-gray-700">61-90 days</p>
                                            <p class="text-xl font-bold text-orange-600 mt-1">UGX {{ number_format($aging60_90 ?? 0, 2) }}</p>
                                            <p class="text-xs text-gray-500 mt-2">Seriously overdue, urgent action required</p>
                                        </div>
                                        <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                            <p class="text-sm font-medium text-gray-700">Over 90 days</p>
                                            <p class="text-xl font-bold text-red-600 mt-1">UGX {{ number_format($agingOver90 ?? 0, 2) }}</p>
                                            <p class="text-xs text-gray-500 mt-2">Very old debts, consider collection</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accounts Receivable Table -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-900">Pending Payments</h2>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @if(auth()->user()->business_id == 1)
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                                                @endif
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Past Due</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aging</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($accountsReceivable as $ar)
                                                <tr class="hover:bg-gray-50">
                                                    @if(auth()->user()->business_id == 1)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $ar->business->name ?? 'N/A' }}
                                                    </td>
                                                    @endif
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $ar->client->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($ar->invoice)
                                                            <a href="{{ route('invoices.show', $ar->invoice->id) }}" class="text-blue-600 hover:text-blue-900">
                                                                {{ $ar->invoice->invoice_number ?? 'N/A' }}
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $ar->invoice_date ? \Carbon\Carbon::parse($ar->invoice_date)->format('M d, Y') : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $ar->due_date ? \Carbon\Carbon::parse($ar->due_date)->format('M d, Y') : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        UGX {{ number_format($ar->amount_due, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        UGX {{ number_format($ar->amount_paid, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                        UGX {{ number_format($ar->balance, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        @if($ar->days_past_due > 0)
                                                            <span class="text-red-600 font-semibold">{{ $ar->days_past_due }} days</span>
                                                        @else
                                                            <span class="text-green-600">{{ $ar->days_past_due }} days</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($ar->status === 'paid')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Paid
                                                            </span>
                                                        @elseif($ar->status === 'overdue')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Overdue
                                                            </span>
                                                        @elseif($ar->status === 'partial')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                Partial
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                Current
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($ar->aging_bucket === 'current')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Current
                                                            </span>
                                                        @elseif($ar->aging_bucket === 'days_30_60')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                30-60
                                                            </span>
                                                        @elseif($ar->aging_bucket === 'days_60_90')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                                60-90
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Over 90
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ auth()->user()->business_id == 1 ? '11' : '10' }}" class="px-6 py-8 text-center text-gray-500">
                                                        No pending payments found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                @if($accountsReceivable->hasPages())
                                    <div class="px-6 py-4 border-t border-gray-200">
                                        {{ $accountsReceivable->links() }}
                                    </div>
                                @endif
                            </div>

                            <!-- Navigation -->
                            <div class="mt-8">
                                <a href="{{ route('dashboard') }}" 
                                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                                    Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

