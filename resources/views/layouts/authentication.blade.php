<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Login – Kashtre</title>
    <meta name="description" content="Secure login to Kashtre – your trusted payment management platform.">
    <meta name="author" content="Kashtre Ltd">

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">

    <main class="min-h-screen flex items-start justify-center pt-16">
        <!-- Centered Form -->
        <div class="w-full max-w-md flex flex-col justify-center px-6 lg:px-24 py-12 bg-white dark:bg-gray-900 rounded">
            <div class="mb-8 flex justify-center">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/kashtre_logo.svg') }}" alt="Kashtre Logo">
                </a>
            </div>

            <div class="w-full mx-auto">
                {{ $slot }}
            </div>
        </div>
    </main>

    @livewireScripts
    @livewireScriptConfig
</body>

</html>
