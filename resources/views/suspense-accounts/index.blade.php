<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div>
                    <h2 class="text-3xl font-bold text-[#011478]">Suspense Accounts Dashboard</h2>
                    <p class="text-gray-600 mt-2">Track and manage all suspense account balances and money movements</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="location.reload()" class="bg-[#011478] hover:bg-[#011478]/90 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Package Suspense Card -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <i class="fas fa-box text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">{{ number_format($totalPackageSuspense, 0) }} UGX</h3>
                        <p class="text-blue-100 font-medium">Package Suspense</p>
                        <p class="text-blue-200 text-sm mt-2">Funds for paid package items</p>
                    </div>

                    <!-- General Suspense Card -->
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">{{ number_format($totalGeneralSuspense, 0) }} UGX</h3>
                        <p class="text-yellow-100 font-medium">General Suspense</p>
                        <p class="text-yellow-200 text-sm mt-2">Ordered items not yet offered</p>
                    </div>

                    <!-- Kashtre Suspense Card -->
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <i class="fas fa-hand-holding-usd text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">{{ number_format($totalKashtreSuspense, 0) }} UGX</h3>
                        <p class="text-green-100 font-medium">Kashtre Suspense</p>
                        <p class="text-green-200 text-sm mt-2">Service fees and deposits</p>
                    </div>

                    <!-- Client Suspense Card -->
                    <div class="bg-gradient-to-r from-gray-500 to-gray-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">{{ number_format($totalClientSuspense, 0) }} UGX</h3>
                        <p class="text-gray-100 font-medium">Client Suspense</p>
                        <p class="text-gray-200 text-sm mt-2">Individual client funds</p>
                    </div>
                </div>

                <!-- Package Suspense Account Records -->
                @php
                    $packageSuspenseAccounts = $suspenseAccounts->where('type', 'package_suspense_account');
                @endphp
                @if($packageSuspenseAccounts->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-box text-blue-500 mr-2"></i>
                                Package Suspense Account Records
                            </h3>
                            <p class="text-gray-600 mt-2">Individual package purchase records - Total: {{ number_format($totalPackageSuspense, 0) }} UGX</p>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($packageSuspenseAccounts as $account)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <i class="fas fa-box text-blue-600"></i>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">#{{ $account->id }}</div>
                                                            <div class="text-sm text-gray-500">{{ $account->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->client->name ?? 'Unknown Client' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->invoice->invoice_number ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-blue-600">
                                                        {{ number_format($account->balance, 0) }} UGX
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->created_at->format('M d, Y H:i') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($account->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- General Suspense Account Records -->
                @php
                    $generalSuspenseAccounts = $suspenseAccounts->where('type', 'general_suspense_account');
                @endphp
                @if($generalSuspenseAccounts->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-clock text-yellow-500 mr-2"></i>
                                General Suspense Account Records
                            </h3>
                            <p class="text-gray-600 mt-2">Individual order records - Total: {{ number_format($totalGeneralSuspense, 0) }} UGX</p>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($generalSuspenseAccounts as $account)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                                                <i class="fas fa-clock text-yellow-600"></i>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">#{{ $account->id }}</div>
                                                            <div class="text-sm text-gray-500">{{ $account->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->client->name ?? 'Unknown Client' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->invoice->invoice_number ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-yellow-600">
                                                        {{ number_format($account->balance, 0) }} UGX
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->created_at->format('M d, Y H:i') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($account->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Kashtre Suspense Account Records -->
                @php
                    $kashtreSuspenseAccounts = $suspenseAccounts->where('type', 'kashtre_suspense_account');
                @endphp
                @if($kashtreSuspenseAccounts->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-hand-holding-usd text-green-500 mr-2"></i>
                                Kashtre Suspense Account Records
                            </h3>
                            <p class="text-gray-600 mt-2">Service fee records - Total: {{ number_format($totalKashtreSuspense, 0) }} UGX</p>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($kashtreSuspenseAccounts as $account)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                                <i class="fas fa-hand-holding-usd text-green-600"></i>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">#{{ $account->id }}</div>
                                                            <div class="text-sm text-gray-500">{{ $account->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->client->name ?? 'Unknown Client' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->invoice->invoice_number ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-green-600">
                                                        {{ number_format($account->balance, 0) }} UGX
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->created_at->format('M d, Y H:i') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($account->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Client Suspense Accounts Table -->
                @if($clientSuspenseAccounts->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-users text-gray-500 mr-2"></i>
                                Client Suspense Accounts
                            </h3>
                            <p class="text-gray-600 mt-2">Individual client funds held in suspense until service delivery</p>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($clientSuspenseAccounts as $account)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                                <i class="fas fa-user text-gray-600"></i>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $account->client->name ?? 'Unknown Client' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $account->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-[#011478]">
                                                        {{ number_format($account->balance, 0) }} UGX
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($account->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                       class="bg-[#011478] hover:bg-[#011478]/90 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent Money Movements -->
                @if($recentMovements->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-exchange-alt text-purple-500 mr-2"></i>
                                Recent Money Movements
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Account</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Account</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentMovements as $movement)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $movement->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $movement->fromAccount->name ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ $movement->toAccount->name ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                    {{ number_format($movement->amount, 0) }} UGX
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $movement->description }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});
</script>
@endpush
