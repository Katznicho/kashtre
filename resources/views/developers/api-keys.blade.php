<x-app-layout>
    <div class="max-w-4xl mx-auto py-12 space-y-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your API Credentials</h2>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="bg-green-100 dark:bg-green-900 p-4 rounded border border-green-300 dark:border-green-700 text-green-800 dark:text-green-100">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="bg-red-100 dark:bg-red-900 p-4 rounded border border-red-300 dark:border-red-700 text-red-800 dark:text-red-100">
                {{ session('error') }}
            </div>
        @endif

        {{-- Credentials --}}
        @if($apiKey)
            @php
                $encoded = base64_encode($apiKey->key . ':' . $apiKey->secret);
            @endphp

            <div class="space-y-4 bg-white dark:bg-gray-800 shadow p-6 rounded border border-gray-200 dark:border-gray-700">
                <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100 text-sm p-3 rounded border border-yellow-400 dark:border-yellow-600">
                    <strong>Important:</strong> These credentials won't be shown again after revoking. Copy or email them now.
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                    <div class="flex items-center gap-2">
                        <input id="apiKey" type="text" value="{{ $apiKey->key }}" readonly
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2 rounded border border-gray-300 dark:border-gray-600 text-sm">
                        <button onclick="copyToClipboard('apiKey')" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                            Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700 dark:text-gray-300 mb-1">API Secret</label>
                    <div class="flex items-center gap-2">
                        <input id="apiSecret" type="text" value="{{ $apiKey->secret }}" readonly
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2 rounded border border-gray-300 dark:border-gray-600 text-sm">
                        <button onclick="copyToClipboard('apiSecret')" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                            Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700 dark:text-gray-300 mb-1">Base64 Authorization Header</label>
                    <div class="flex items-center gap-2">
                        <input id="apiEncoded" type="text" value="{{ $encoded }}" readonly
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2 rounded border border-gray-300 dark:border-gray-600 text-sm">
                        <button onclick="copyToClipboard('apiEncoded')" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            {{-- Key info --}}
            <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-700 dark:text-gray-300">Generated On: <strong>{{ $apiKey->created_at->format('Y-m-d H:i') }}</strong></p>
                <p class="text-xs mt-2 text-gray-500 dark:text-gray-400">You can revoke and regenerate credentials below.</p>
            </div>
        @else
            <div class="bg-yellow-50 text-yellow-800 p-4 rounded border border-yellow-200">
                No active API credentials found. Generate new credentials below.
            </div>
        @endif

        {{-- Generate button --}}
        <form method="POST" action="{{ route('api-keys.generate') }}">
            @csrf
            <button type="submit"
                class="bg-[#011478] text-white px-4 py-2 rounded hover:bg-[#011478]/90 transition text-sm">
                🔁 Generate New API Key
            </button>
        </form>

        {{-- Email Button --}}
        @if($apiKey)
            <form method="POST" action="{{ route('api-keys.email') }}">
                @csrf
                <button type="submit"
                    class="mt-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 transition text-sm">
                    📧 Email Me These Credentials
                </button>
            </form>
        @endif
    </div>

    {{-- SweetAlert Copy Script --}}
    <script>
        function copyToClipboard(id) {
            const input = document.getElementById(id);
            input.select();
            input.setSelectionRange(0, 99999); // For mobile
            navigator.clipboard.writeText(input.value).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Copied to clipboard.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Could not copy. Try again.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                });
            });
        }
    </script>
</x-app-layout>
