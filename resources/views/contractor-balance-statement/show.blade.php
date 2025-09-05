<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $contractorProfile->user->name ?? 'Contractor' }} - Balance Statement
            </h2>
            <a href="{{ route('contractor-balance-statement.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to All Contractors
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Contractor Account Statement</h1>
                        <p class="text-gray-600">Detailed financial overview for {{ $contractorProfile->user->name ?? 'Contractor' }}</p>
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Available Balance -->
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Available Balance</h3>
                                    <p class="text-2xl font-bold text-blue-600">
                                        UGX {{ number_format($contractorBalanceHistories->where('type', 'credit')->sum('amount') - $contractorBalanceHistories->where('type', 'debit')->sum('amount'), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Balance -->
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Total Balance</h3>
                                    <p class="text-2xl font-bold text-green-600">
                                        UGX {{ number_format($contractorBalanceHistories->where('type', 'credit')->sum('amount'), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Debits -->
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-red-100 text-red-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Total Debits</h3>
                                    <p class="text-2xl font-bold text-red-600">
                                        UGX {{ number_format($contractorBalanceHistories->where('type', 'debit')->sum('amount'), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Transaction History</h2>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($contractorBalanceHistories as $history)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($history->type === 'credit')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Credit
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Debit
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($history->type === 'credit')
                                                    <span class="text-green-600">+{{ number_format($history->amount, 2) }} UGX</span>
                                                @else
                                                    <span class="text-red-600">-{{ number_format($history->amount, 2) }} UGX</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="max-w-xs">
                                                    {{ $history->description }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($history->payment_method)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ ucfirst(str_replace('_', ' ', $history->payment_method)) }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $history->created_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                                No transactions found for this contractor.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($contractorBalanceHistories->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $contractorBalanceHistories->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Navigation -->
                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('dashboard') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                            Back to Dashboard
                        </a>
                        
                        <a href="{{ route('contractor-balance-statement.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            View All Contractors
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

