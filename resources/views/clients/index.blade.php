<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Clients Management</h1>
                        <p class="text-gray-600 mt-2">{{ $business->name }} - {{ $currentBranch->name }}</p>
                        <p class="text-sm text-blue-600 mt-1">Showing clients for: <strong>{{ $selectedBranch->name }}</strong></p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('clients.create') }}" class="bg-[#011478] text-white px-6 py-3 rounded-lg hover:bg-[#011478]/90 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New Client
                        </a>
                        <div class="text-xs text-gray-500 mt-1">New clients will be registered at: <strong>{{ $currentBranch->name }}</strong></div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Clients</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $clients->total() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-calendar-day text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Today's Clients</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $todayClients }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Selected Branch</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $selectedBranch->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Filament Table Component -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Registered Clients</h2>
                    </div>
                    
                    <div class="p-6">
                        @livewire('clients-table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
