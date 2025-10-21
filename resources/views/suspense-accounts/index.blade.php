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

                <!-- Suspense Accounts Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Package Suspense Account -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-box text-blue-500 mr-2"></i>
                                Package Suspense Account
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                <strong>Purpose:</strong> Holds funds for paid package items if nothing has been used.
                            </p>
                            @if($suspenseAccounts->where('type', 'package_suspense_account')->count() > 0)
                                @foreach($suspenseAccounts->where('type', 'package_suspense_account') as $account)
                                    <div class="flex justify-between items-center mb-3 p-3 bg-blue-50 rounded-lg">
                                        <span class="font-medium text-gray-700">{{ $account->name }}</span>
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            {{ number_format($account->balance, 0) }} UGX
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">No package suspense accounts found.</p>
                            @endif
                        </div>
                    </div>

                    <!-- General Suspense Account -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-clock text-yellow-500 mr-2"></i>
                                General Suspense Account
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                <strong>Purpose:</strong> Holds funds for ordered items not yet offered for all clients.
                            </p>
                            @if($suspenseAccounts->where('type', 'general_suspense_account')->count() > 0)
                                @foreach($suspenseAccounts->where('type', 'general_suspense_account') as $account)
                                    <div class="flex justify-between items-center mb-3 p-3 bg-yellow-50 rounded-lg">
                                        <span class="font-medium text-gray-700">{{ $account->name }}</span>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            {{ number_format($account->balance, 0) }} UGX
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">No general suspense accounts found.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kashtre Suspense Account -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Kashtre Suspense Account -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-hand-holding-usd text-green-500 mr-2"></i>
                                Kashtre Suspense Account
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                <strong>Purpose:</strong> Holds all service fees charged on invoices and deposits for services paid but not yet offered.
                            </p>
                            @if($suspenseAccounts->where('type', 'kashtre_suspense_account')->count() > 0)
                                @foreach($suspenseAccounts->where('type', 'kashtre_suspense_account') as $account)
                                    <div class="flex justify-between items-center mb-3 p-3 bg-green-50 rounded-lg">
                                        <span class="font-medium text-gray-700">{{ $account->name }}</span>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            {{ number_format($account->balance, 0) }} UGX
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">No Kashtre suspense accounts found.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Client Suspense Accounts -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-users text-gray-500 mr-2"></i>
                                Client Suspense Accounts
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                <strong>Purpose:</strong> Individual client funds held in suspense until service delivery.
                            </p>
                            @if($clientSuspenseAccounts->count() > 0)
                                <div class="space-y-3">
                                    @foreach($clientSuspenseAccounts->take(5) as $account)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <span class="font-medium text-gray-700">{{ $account->client->name ?? 'Unknown Client' }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                    {{ number_format($account->balance, 0) }} UGX
                                                </span>
                                                <a href="{{ route('suspense-accounts.show', $account->id) }}" 
                                                   class="bg-[#011478] hover:bg-[#011478]/90 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($clientSuspenseAccounts->count() > 5)
                                        <p class="text-gray-500 text-sm italic">... and {{ $clientSuspenseAccounts->count() - 5 }} more clients</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-gray-500 italic">No client suspense accounts found.</p>
                            @endif
                        </div>
                    </div>
                </div>

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
