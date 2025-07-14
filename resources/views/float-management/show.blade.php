<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Float Details ({{ $float->uuid }})</h1>

        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <div>
                <strong>User:</strong>
                {{ $float->user ? $float->user->name : 'N/A' }}
            </div>

            <div>
                <strong>Business:</strong>
                {{ $float->business ? $float->business->name : 'N/A' }}
            </div>

            <div>
                <strong>Amount:</strong>
                {{ number_format($float->amount, 2) }} {{ $float->currency }}
            </div>

            <div>
                <strong>Status:</strong>
                <span class="capitalize">{{ $float->status }}</span>
            </div>

            <div>
                <strong>Channel:</strong>
                {{ $float->channel }}
            </div>

            <div>
                <strong>Date Loaded:</strong>
                {{ \Carbon\Carbon::parse($float->date)->format('F j, Y') }}
            </div>

            <div>
                <strong>Proof:</strong><br>

                @php
                    $proofUrl = $float->proof ? Storage::disk('public')->url($float->proof) : null;
                    $ext = $float->proof ? pathinfo($float->proof, PATHINFO_EXTENSION) : null;
                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                @endphp

                @if ($proofUrl)
                    @if (in_array(strtolower($ext), $imageExtensions))
                        <img src="{{ $proofUrl }}" alt="Proof Image" class="max-w-md rounded shadow mb-2" />
                        <br>
                        <a href="{{ $proofUrl }}" target="_blank" class="text-blue-600 underline">Download Proof</a>
                    @else
                        <a href="{{ $proofUrl }}" target="_blank" class="text-blue-600 underline">
                            Download Proof ({{ strtoupper($ext) }})
                        </a>
                    @endif
                @else
                    <p class="text-gray-500 italic">No proof uploaded.</p>
                @endif
            </div>

            <div>
                <strong>Created At:</strong>
                {{ $float->created_at->format('F j, Y, g:i a') }}
            </div>

            <div>
                <strong>Updated At:</strong>
                {{ $float->updated_at->format('F j, Y, g:i a') }}
            </div>

            <div class="mt-6">
                <a href="{{ route('float-management.index') }}"
                    class="inline-block px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Back to List</a>
            </div>
        </div>
    </div>
</x-app-layout>
