<x-app-layout>
    <div x-data="{ showModal: false, channel: 'Bank - Deposit' }" @keydown.escape.window="showModal = false" x-cloak class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Your Float ({{ env('APP_NAME') }})</h2>

                    <button @click="showModal = true"
                        class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                        ➕ Request Top-Up
                    </button>
                </div>

                {{-- Your livewire float list component --}}
                @livewire('float.list-float-management')
            </div>
        </div>

        <!-- Float Top-Up Modal -->
        <div x-show="showModal" x-transition.opacity
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
            style="display: none;">
            <div @click.away="showModal = false" x-transition
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Request Float Top-Up</h3>
                    <button @click="showModal = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                        ✕
                    </button>
                </div>

                <!-- Bank Info (shown if Bank - Deposit selected) -->
                <div x-show="channel === 'Bank - Deposit'" class="text-sm bg-blue-50 dark:bg-gray-700 dark:text-gray-200 p-4 rounded mb-6 border border-blue-200" x-cloak>
                    <p><strong>Deposits should be made to the MarzPay account:</strong></p>
                    <p><strong>Account Name:</strong> MARZPAY LTD</p>
                    <p><strong>Account Number:</strong> 9030012345678</p>
                    <p><strong>Bank Name:</strong> STANBIC BANK UGANDA</p>
                    <p><strong>Currency:</strong> UGX</p>
                    <p><strong>Swift Code:</strong> SBICUGKXXXX</p>
                    <p><strong>Branch:</strong> KAMPALA ROAD BRANCH</p>
                </div>

                <!-- Mobile Money Info (shown if Mobile Money selected) -->
                <div x-show="channel === 'Mobile Money'" class="text-sm bg-green-50 dark:bg-gray-700 dark:text-gray-200 p-4 rounded mb-6 border border-green-200" x-cloak>
                    <p><strong>Mobile Money Deposits:</strong></p>
                    <p><strong>Airtel Money Merchant Code:</strong> 123456</p>
                    <p><strong>MTN Mobile Money Merchant Code:</strong> 654321</p>
                    <p>Please send money via your mobile money app to the above merchant codes and upload the transaction reference as proof.</p>
                </div>

                <!-- Top-Up Form -->
                <form action="{{ route('float-management.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label for="currency" class="block font-medium text-gray-700 dark:text-gray-300">Currency</label>
                        <select name="currency" id="currency" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="UGX" selected>UGX</option>
                            <option value="USD" disabled>USD (Coming Soon)</option>
                            <option value="KES" disabled>KES (Coming Soon)</option>
                            <option value="TZS" disabled>TZS (Coming Soon)</option>
                        </select>
                        <p class="text-xs text-yellow-600 mt-1 italic">Only UGX is supported at the moment.</p>
                    </div>

                    <div>
                        <label for="channel" class="block font-medium text-gray-700 dark:text-gray-300">Channel</label>
                        <select name="channel" id="channel" x-model="channel" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="Bank - Deposit">Bank - Deposit</option>
                            <option value="Mobile Money">Mobile Money</option>
                        </select>
                    </div>

                    <div>
                        <label for="amount" class="block font-medium text-gray-700 dark:text-gray-300">Amount</label>
                        <input type="number" step="0.01" name="amount" id="amount" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="e.g. 100000">
                    </div>

                    <div>
                        <label for="date_loaded" class="block font-medium text-gray-700 dark:text-gray-300">Date Loaded</label>
                        <input type="date" name="date_loaded" id="date_loaded" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="proof" class="block font-medium text-gray-700 dark:text-gray-300">Reference Scan / Proof</label>
                        <input type="file" name="proof" id="proof" required accept="image/*,application/pdf"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">
                            Request Float Top-Up
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
