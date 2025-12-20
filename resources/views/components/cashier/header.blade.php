<header class="sticky top-0 z-30 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 -mb-px">
            <!-- Mobile sidebar toggle -->
            <button class="lg:hidden text-gray-500 hover:text-gray-400" @click.stop="sidebarOpen = !sidebarOpen" aria-controls="sidebar" :aria-expanded="sidebarOpen">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
                </svg>
            </button>

            <!-- Page title -->
            <div class="flex-1">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">
                    @if(isset($title))
                        {{ $title }}
                    @else
                        Cashier Dashboard
                    @endif
                </h1>
            </div>

            <!-- User menu -->
            <div class="flex items-center space-x-4">
                @php
                    $user = Auth::user();
                @endphp
                @if($user)
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">Cashier</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</header>

