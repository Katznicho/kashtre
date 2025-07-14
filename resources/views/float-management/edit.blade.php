<x-app-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Edit Float ({{ $float->uuid }})</h1>

        <form action="{{ route('float-management.update', $float) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="currency" class="block font-medium text-gray-700 dark:text-gray-300">Currency</label>
                <select name="currency" id="currency" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="UGX" {{ $float->currency === 'UGX' ? 'selected' : '' }}>UGX</option>
                    <option value="USD" disabled>USD (Coming Soon)</option>
                    <option value="KES" disabled>KES (Coming Soon)</option>
                    <option value="TZS" disabled>TZS (Coming Soon)</option>
                </select>
                @error('currency')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="channel" class="block font-medium text-gray-700 dark:text-gray-300">Channel</label>
                <select name="channel" id="channel" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="Bank - Deposit" {{ $float->channel === 'Bank - Deposit' ? 'selected' : '' }}>Bank - Deposit</option>
                    <option value="Mobile Money" {{ $float->channel === 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                </select>
                @error('channel')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block font-medium text-gray-700 dark:text-gray-300">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" required
                    value="{{ old('amount', $float->amount) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="e.g. 100000">
                @error('amount')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date_loaded" class="block font-medium text-gray-700 dark:text-gray-300">Date Loaded</label>
                <input type="date" name="date_loaded" id="date_loaded" required
                    value="{{ old('date_loaded', $float->date ? \Carbon\Carbon::parse($float->date)->format('Y-m-d') : '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @error('date_loaded')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Current Proof</label>
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
                <label for="proof" class="block font-medium text-gray-700 dark:text-gray-300">Replace Proof (Optional)</label>
                <input type="file" name="proof" id="proof" accept="image/*,application/pdf"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 mt-1">Upload a new file to replace the existing proof.</p>
                @error('proof')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <a href="{{ route('float-management.index') }}"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>
                <button type="submit"
                    class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">Update Float</button>
            </div>
        </form>
    </div>
</x-app-layout>
