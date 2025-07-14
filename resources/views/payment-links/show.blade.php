<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50 dark:bg-gray-900">
        <div
            class="w-full max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">

            {{-- Business Logo --}}
            @php
                $logoPath = $paymentLink->business?->logo;
                $businessName = $paymentLink->business?->name ?? 'MarzPay';
            @endphp

            <div class="flex justify-center mb-4 w-32 h-16 mx-auto">
                @if ($logoPath && file_exists(public_path('storage/' . $logoPath)))
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Business Logo"
                        class="w-full h-full object-contain">
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="Default Logo" class="w-full h-full object-contain">
                @endif
            </div>

            {{-- Business Name --}}
            <h2 class="text-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                {{ $businessName }}
            </h2>

            {{-- Title and Description --}}
            <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-2">
                {{ $paymentLink->title }}
            </h1>
            <p class="text-center text-gray-600 dark:text-gray-400 mb-6">
                {{ $paymentLink->description ?? 'Complete your payment using the form below.' }}
            </p>

            {{-- Payment Form --}}
            <form action="{{ route('public.payment.pay', $paymentLink) }}" method="POST" class="space-y-5">
                @csrf

                {{-- Hidden business_id --}}
                @if ($paymentLink->business_id)
                    <input type="hidden" name="business_id" value="{{ $paymentLink->business_id }}">
                @endif

                @if ($paymentLink->is_fixed)
                    <div class="text-center text-lg text-gray-800 dark:text-white mb-4">
                        Amount: <strong>UGX {{ number_format($paymentLink->amount) }}</strong>
                    </div>
                @else
                    <div>
                        <label for="amount"
                            class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" name="amount" id="amount" min="{{ $paymentLink->minimum_amount }}"
                            required placeholder="Minimum: UGX {{ number_format($paymentLink->minimum_amount) }}"
                            class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                @endif

                {{-- payment phone number --}}
                @if ($paymentLink->method === 'mobile_money')
                    <div class="flex">
                        <span
                            class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white text-sm">
                            +256
                        </span>
                        <input type="tel" name="phone_number" id="phone_number" required pattern="[0-9]{9}"
                            maxlength="9" placeholder="7XXXXXXXX"
                            class="w-full px-4 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <small class="text-sm text-gray-500 dark:text-gray-400">Do not include +256 or 0</small>
                @endif

                {{-- Customer Info Fields --}}
                @php
                    $fields = json_decode($paymentLink->customer_fields ?? '[]');
                @endphp

                @if ($paymentLink->is_customer_info_required)
                    @if (in_array('name', $fields))
                        <div>
                            <label for="name"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input type="text" name="name" id="name" required
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    @endif

                    @if (in_array('email', $fields))
                        <div>
                            <label for="email"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" id="email" required
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    @endif

                    @if (in_array('phone_number', $fields))
                        <div>
                            <label for="phone_number"
                                class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                            <input type="tel" name="phone_number" id="phone_number" required
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    @endif
                @endif

                <button type="submit"
                    class="w-full bg-[#011478] hover:bg-[#011478]/90 text-white py-3 rounded-md font-semibold transition duration-200 shadow-md">
                    Pay Now
                </button>
            </form>

            {{-- Powered By --}}
            <div class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
                This payment is powered by <span
                    class="font-semibold text-[#011478]">{{ env('APP_NAME') }}</span>
            </div>
        </div>
    </div>
</x-guest-layout>
