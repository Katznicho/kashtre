<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show"
                        class="relative bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 transition"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button @click="show = false"
                            class="absolute top-1 right-2 text-xl font-semibold text-green-700">
                            &times;
                        </button>
                    </div>
                @endif

                <style>
                    /* Force style all Filament buttons on this page */
                    button[wire\:click],
                    .fi-ta-header-toolbar button,
                    .fi-ta-empty-state-actions button,
                    .fi-btn,
                    [class*="fi-btn"],
                    .filament-tables-header-actions button,
                    .filament-button {
                        background-color: #2563eb !important;
                        color: #ffffff !important;
                        border-radius: 0.5rem !important;
                        padding: 0.5rem 1.25rem !important;
                        font-weight: 600 !important;
                        border: none !important;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
                    }
                    button[wire\:click]:hover,
                    .fi-ta-header-toolbar button:hover,
                    .fi-ta-empty-state-actions button:hover,
                    .fi-btn:hover,
                    [class*="fi-btn"]:hover {
                        background-color: #1d4ed8 !important;
                    }
                </style>

                @livewire('client-spaces.list-client-spaces')

            </div>
        </div>
    </div>
</x-app-layout>
