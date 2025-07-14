<x-app-layout>
    <div class="py-12" x-data="{ showModal: false }" @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manage Payment Links</h2>

                    <button @click="showModal = true"
                        class="inline-flex items-center px-4 py-2 bg-[#011478] text-white text-sm font-semibold rounded-md hover:bg-[#011478]/90 transition duration-150">
                        ➕ Create Payment Link
                    </button>
                </div>

                @livewire('links.list-payment-liks')
            </div>
        </div>

        <!-- Modal backdrop -->
        <div x-show="showModal" x-transition.opacity
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
            style="display: none;">
            <!-- Modal panel -->
            <div @click.away="showModal = false" x-transition
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Create Payment Link</h3>
                    <button @click="showModal = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                        ✕
                    </button>
                </div>
               <form action="{{ route('payment-links.store') }}" method="POST" enctype="multipart/form-data"
    class="space-y-6"
    x-data="{
        isFixed: true,
        requireCustomerInfo: false,
        hasExpiry: false,
        method: 'mobile_money'
    }">
    @csrf

    <div>
        <label for="title" class="block font-medium text-gray-700 dark:text-gray-300">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title') }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="e.g. School Fees Payment">
        @error('title') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type" class="block font-medium text-gray-700 dark:text-gray-300">Link Type</label>
        <select name="type" id="type" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">-- Select Type --</option>
            <option value="payment">Payment</option>
            <option value="donation">Donation</option>
            <option value="subscription">Subscription</option>
        </select>
        @error('type') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center space-x-3">
        <input type="checkbox" name="is_fixed" id="is_fixed" value="1" x-model="isFixed" class="mr-2">
        <label for="is_fixed" class="text-gray-700 dark:text-gray-300">Is Fixed Amount?</label>
    </div>

    <div x-show="isFixed">
        <label for="amount" class="block font-medium text-gray-700 dark:text-gray-300">Amount</label>
        <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="e.g. 50000">
        @error('amount') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div x-show="!isFixed">
        <label for="minimum_amount" class="block font-medium text-gray-700 dark:text-gray-300">Minimum Amount</label>
        <input type="number" step="0.01" name="minimum_amount" id="minimum_amount" value="{{ old('minimum_amount') }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="e.g. 1000">
        @error('minimum_amount') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="block font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" id="description" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Brief details about the payment link">{{ old('description') }}</textarea>
        @error('description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="redirect_url" class="block font-medium text-gray-700 dark:text-gray-300">Redirect URL (optional)</label>
        <input type="url" name="redirect_url" id="redirect_url" value="{{ old('redirect_url') }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="https://example.com/thank-you">
        @error('redirect_url') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center space-x-3">
        <input type="checkbox" id="has_expiry" x-model="hasExpiry" class="mr-2">
        <label for="has_expiry" class="text-gray-700 dark:text-gray-300">Add Expiry Date?</label>
    </div>

    <div x-show="hasExpiry">
        <label for="expiry_date" class="block font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
        <input type="datetime-local" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('expiry_date') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    <!-- Payment Method Selection -->
    <div class="space-y-4">
        <div>
            <label for="method" class="block font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
            <select name="method" id="method" x-model="method" required
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="mobile_money">Mobile Money</option>
                <option value="card">Card (Coming Soon)</option>
                <option value="bank_transfer">Bank Transfer (Coming Soon)</option>
                <option value="crypto">Crypto (Coming Soon)</option>
            </select>
            @error('method') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <!-- Mobile Money Fields -->
        {{-- <div x-show="method === 'mobile_money'" class="transition">
            <label for="mobile_money_number" class="block font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
            <input type="text" name="mobile_money_number" id="mobile_money_number"
                value="{{ old('mobile_money_number') }}"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="e.g. 0770123456">
            @error('mobile_money_number') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div> --}}

        <!-- Coming Soon Notice -->
        <div x-show="method !== 'mobile_money'" class="text-yellow-600 text-sm italic">
            This payment method is not yet available. Please select Mobile Money.
        </div>
    </div>

    <!-- Customer Info Fields -->
    <div>
        <div class="flex items-center space-x-3 mt-4">
            <input type="checkbox" name="is_customer_info_required" id="is_customer_info_required"
                value="1" x-model="requireCustomerInfo" class="mr-2"
                {{ old('is_customer_info_required') ? 'checked' : '' }}>
            <label for="is_customer_info_required" class="text-gray-700 dark:text-gray-300">Require Customer Info?</label>
        </div>

        <template x-if="requireCustomerInfo">
            <div class="flex flex-col gap-2 mt-3">
                <span class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Select customer info to collect</span>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="customer_fields[]" value="name" class="rounded text-blue-600"
                        {{ is_array(old('customer_fields')) && in_array('name', old('customer_fields')) ? 'checked' : '' }}>
                    <span class="text-gray-700 dark:text-gray-300">Name</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="customer_fields[]" value="email" class="rounded text-blue-600"
                        {{ is_array(old('customer_fields')) && in_array('email', old('customer_fields')) ? 'checked' : '' }}>
                    <span class="text-gray-700 dark:text-gray-300">Email</span>
                </label>
                {{-- <label class="flex items-center space-x-2">
                    <input type="checkbox" name="customer_fields[]" value="phone_number" class="rounded text-blue-600"
                        {{ is_array(old('customer_fields')) && in_array('phone_number', old('customer_fields')) ? 'checked' : '' }}>
                    <span class="text-gray-700 dark:text-gray-300">Phone Number</span>
                </label> --}}
            </div>
        </template>
    </div>

    <div class="flex justify-end space-x-4 pt-4">
        <button type="button" @click="showModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
            Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-[#011478] text-white rounded-md hover:bg-[#011478]/90">
            Create Link
        </button>
    </div>
</form>



            </div>
        </div>
    </div>


    <script>
        window.addEventListener('copy-to-clipboard', event => {
            navigator.clipboard.writeText(event.detail.url)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied!',
                        text: 'Link copied to clipboard!',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                    });
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Failed to copy link.',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                    });
                });
        });
    </script>

</x-app-layout>
