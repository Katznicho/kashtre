<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Settings - Countries & Exchange Rates') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                            <ul class="text-sm text-red-700 list-disc ml-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Country</h3>
                            <form method="POST" action="{{ route('settings.countries.store') }}" id="create-country-form">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                        <select name="name" id="create-country-name" class="w-full border-gray-300 rounded-md" required>
                                            <option value="">Select country</option>
                                            @foreach($countryOptions as $option)
                                                <option value="{{ $option['name'] }}" data-iso="{{ $option['iso_code'] }}" data-currency="{{ $option['currency_code'] }}">
                                                    {{ $option['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ISO Code</label>
                                        <input type="text" name="iso_code" id="create-country-iso" class="w-full border-gray-300 rounded-md" placeholder="e.g. UG" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                        <select name="currency_code" id="create-country-currency" class="w-full border-gray-300 rounded-md" required>
                                            <option value="">Select currency code</option>
                                            @foreach($currencyOptions as $currencyCode)
                                                <option value="{{ $currencyCode }}">{{ $currencyCode }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Exchange Rate to USD</label>
                                        <input type="number" step="0.000001" min="0.000001" name="exchange_rate_to_usd" class="w-full border-gray-300 rounded-md" placeholder="e.g. 0.000270" required>
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                                        Save Country
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Country</h3>
                            @if($editingCountry)
                                <form method="POST" action="{{ route('settings.countries.update', $editingCountry) }}" id="edit-country-form">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                            <select name="name" id="edit-country-name" class="w-full border-gray-300 rounded-md" required>
                                                <option value="">Select country</option>
                                                @foreach($countryOptions as $option)
                                                    <option
                                                        value="{{ $option['name'] }}"
                                                        data-iso="{{ $option['iso_code'] }}"
                                                        data-currency="{{ $option['currency_code'] }}"
                                                        {{ $editingCountry->name === $option['name'] ? 'selected' : '' }}
                                                    >
                                                        {{ $option['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">ISO Code</label>
                                            <input type="text" name="iso_code" id="edit-country-iso" class="w-full border-gray-300 rounded-md" value="{{ old('iso_code', $editingCountry->iso_code) }}" placeholder="e.g. UG" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                            <select name="currency_code" id="edit-country-currency" class="w-full border-gray-300 rounded-md" required>
                                                <option value="">Select currency code</option>
                                                @foreach($currencyOptions as $currencyCode)
                                                    <option value="{{ $currencyCode }}" {{ old('currency_code', $editingCountry->currency_code) === $currencyCode ? 'selected' : '' }}>
                                                        {{ $currencyCode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Exchange Rate to USD</label>
                                            <input type="number" step="0.000001" min="0.000001" name="exchange_rate_to_usd" class="w-full border-gray-300 rounded-md" value="{{ old('exchange_rate_to_usd', $editingCountry->exchange_rate_to_usd) }}" placeholder="e.g. 0.000270" required>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                                                Update Country
                                            </button>
                                            <a href="{{ route('settings.countries.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel Edit</a>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <p class="text-sm text-gray-500">Select a country from the table below to edit.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Saved Countries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ISO</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rate to USD</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($countries as $country)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $country->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $country->iso_code }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $country->currency_code }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ number_format((float) $country->exchange_rate_to_usd, 6) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <a href="{{ route('settings.countries.index', ['edit' => $country->id]) }}" class="text-blue-600 hover:text-blue-800 mr-4">Edit</a>
                                                <form method="POST" action="{{ route('settings.countries.destroy', $country) }}" class="inline" onsubmit="return confirm('Delete this country?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-sm text-gray-500">No countries found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $countries->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function wireCountrySelector(countrySelectId, isoInputId, currencySelectId) {
            const countrySelect = document.getElementById(countrySelectId);
            const isoInput = document.getElementById(isoInputId);
            const currencySelect = document.getElementById(currencySelectId);
            if (!countrySelect || !isoInput || !currencySelect) return;

            countrySelect.addEventListener('change', function () {
                const selected = countrySelect.options[countrySelect.selectedIndex];
                const iso = selected?.getAttribute('data-iso') || '';
                const currency = selected?.getAttribute('data-currency') || '';
                if (iso) isoInput.value = iso;
                if (currency) currencySelect.value = currency;
            });
        }

        wireCountrySelector('create-country-name', 'create-country-iso', 'create-country-currency');
        wireCountrySelector('edit-country-name', 'edit-country-iso', 'edit-country-currency');
    </script>
</x-app-layout>

