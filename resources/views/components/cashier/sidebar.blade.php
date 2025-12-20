<div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 bg-gray-900/30 z-40 lg:hidden lg:z-auto transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" x-cloak></div>

    <!-- Sidebar -->
    <div id="sidebar" class="flex flex-col absolute z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-[100dvh] overflow-y-scroll lg:overflow-y-auto no-scrollbar w-64 lg:w-20 lg:sidebar-expanded:!w-64 2xl:!w-64 shrink-0 bg-white dark:bg-gray-800 p-4 transition-all duration-200 ease-in-out rounded-r-2xl shadow-xs" :class="sidebarOpen ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-64'" @click.outside="sidebarOpen = false" @keydown.escape.window="sidebarOpen = false">

        <!-- Sidebar header -->
        <div class="flex justify-between mb-10 pr-3 sm:px-2">
            <!-- Close button -->
            <button class="lg:hidden text-gray-500 hover:text-gray-400" @click.stop="sidebarOpen = !sidebarOpen" aria-controls="sidebar" :aria-expanded="sidebarOpen">
                <span class="sr-only">Close sidebar</span>
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
                </svg>
            </button>

            <!-- Logo and Business Info -->
            <div class="flex flex-col items-center w-full">
                <h1 class="text-[#011478] font-extrabold text-xl mb-1">{{ env('APP_NAME') }}</h1>
                <div class="w-16 h-16 rounded-lg overflow-hidden">
                    <img src="{{ asset('images/kashtre_logo.svg') }}" alt="Default Logo" class="w-full h-full object-contain">
                </div>
                @php
                    $user = Auth::user();
                    $business = $user ? $user->business : null;
                @endphp
                @if($business)
                <h2 class="text-[#011478] font-bold text-sm mt-2 text-center">
                    {{ $business->name }}
                </h2>
                <p class="text-gray-500 text-xs mt-0.5">
                    Cashier Portal
                </p>
                @endif
            </div>
        </div>

        <!-- Links -->
        <div class="space-y-8">
            <div>
                <ul class="mt-3 space-y-2">

                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('cashier-dashboard.index') }}" class="flex items-center pl-4 pr-3 py-2 rounded-lg {{ request()->routeIs('cashier-dashboard.index') ? 'bg-blue-100 text-blue-900 font-semibold' : 'text-gray-700 hover:text-blue-700 hover:bg-blue-50' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            <span class="text-sm font-medium ml-3 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Dashboard</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <!-- Logout -->
        <div class="pt-4 mt-auto">
            <form method="POST" action="{{ route('cashier.logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full pl-4 pr-3 py-2 rounded-lg text-gray-700 hover:text-red-700 hover:bg-red-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="text-sm font-medium ml-3 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

