<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div>
                    <h2 class="text-3xl font-bold text-[#011478]">Service Points</h2>
                    <p class="text-gray-600 mt-2">Click on a service point to manage its queues</p>
                </div>
            </div>

            @if($servicePoints->count() > 0)
                <!-- Service Points Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($servicePoints as $servicePoint)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer"
                             onclick="window.location.href='{{ route('service-points.show', $servicePoint->id) }}'">
                            
                            <!-- Service Point Header -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $servicePoint->name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $servicePoint->branch->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="p-6">
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-yellow-600">{{ $servicePoint->queue_stats['pending'] }}</div>
                                        <div class="text-xs text-gray-600">Pending</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-orange-600">{{ $servicePoint->queue_stats['partially_done'] }}</div>
                                        <div class="text-xs text-gray-600">In Progress</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-green-600">{{ $servicePoint->queue_stats['completed_today'] }}</div>
                                        <div class="text-xs text-gray-600">Completed</div>
                                    </div>
                                </div>

                                <!-- Quick Preview -->
                                <div class="text-center">
                                    <div class="text-sm text-gray-600 mb-2">
                                        @if($servicePoint->queue_stats['pending'] > 0)
                                            <span class="text-yellow-600 font-medium">{{ $servicePoint->queue_stats['pending'] }} clients waiting</span>
                                        @elseif($servicePoint->queue_stats['partially_done'] > 0)
                                            <span class="text-orange-600 font-medium">{{ $servicePoint->queue_stats['partially_done'] }} in progress</span>
                                        @else
                                            <span class="text-green-600 font-medium">No active clients</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-blue-600 font-medium">
                                        Click to manage queues →
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Service Points -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Service Points Found</h3>
                    <p class="text-gray-600">No service points are currently assigned to your account.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
