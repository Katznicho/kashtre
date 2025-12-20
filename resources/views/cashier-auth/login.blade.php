<x-authentication-layout>
    <div class="text-center mb-6">
        <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white">Cashier Login</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Access your cashier dashboard</p>
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Login Form -->
    <form method="POST" action="{{ route('cashier.login') }}" class="space-y-6">
        @csrf

        <div>
            <x-label for="email" value="Email Address" />
            <x-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
        </div>

        <div>
            <x-label for="password" value="Password" />
            <x-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div class="flex items-center">
            <input id="remember" type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                Remember me
            </label>
        </div>

        <div>
            <x-button class="w-full justify-center">
                🔐 Sign in
            </x-button>
        </div>

        <x-validation-errors class="mt-4" />
    </form>

    <!-- Alternative Login Links -->
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="text-center space-y-3">
            <p class="text-sm text-gray-600 dark:text-gray-400">Not a cashier?</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    Staff Login
                </a>
                <span class="hidden sm:inline text-gray-400">|</span>
                <a href="{{ route('third-party-payer.login') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    Third-Party Payer Login
                </a>
            </div>
        </div>
    </div>
</x-authentication-layout>

