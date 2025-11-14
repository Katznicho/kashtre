<div>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Payment Methods and Maturation Periods
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Configure payment method accounts and maturation periods for different payment methods by entity
                    </p>
                </div>
            </div>

            <!-- Filament Table -->
            <div class="bg-white shadow rounded-lg">
                {{ $this->table }}
            </div>
        </div>
    </div>
</div>
