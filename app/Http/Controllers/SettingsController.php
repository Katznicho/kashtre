<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Currency;
use App\Models\Country;
use App\Models\InsuranceCompany;

class SettingsController extends Controller
{
    /**
     * Settings page: third party vendors only.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (
            (int) ($user->business_id ?? 0) !== 1
            && !in_array('View Insurance Companies', $user->permissions ?? [])
        ) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view settings.');
        }

        // Backward compatibility for old tab URL.
        if ($request->get('tab') === 'currencies') {
            return redirect()->route('settings.countries.index');
        }

        $insuranceCompanies = InsuranceCompany::with('business')
            ->latest()
            ->paginate(15);

        return view('settings.index', compact('insuranceCompanies'));
    }

    /**
     * Countries and exchange rates page (separate from vendors settings).
     */
    public function countriesIndex()
    {
        $user = Auth::user();
        if ((int) ($user->business_id ?? 0) !== 1) {
            abort(403);
        }

        $countries = Country::latest()->paginate(15);
        $editingCountry = null;
        $editId = (int) request()->query('edit', 0);
        if ($editId > 0) {
            $editingCountry = Country::find($editId);
        }

        return view('settings.countries', [
            'countries' => $countries,
            'editingCountry' => $editingCountry,
            'countryOptions' => $this->countryOptions(),
            'currencyOptions' => $this->currencyOptions(),
        ]);
    }

    public function storeCountry(Request $request)
    {
        $user = Auth::user();
        if ((int) ($user->business_id ?? 0) !== 1) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:10|unique:countries,iso_code',
            'currency_code' => 'required|string|max:10',
            'exchange_rate_to_usd' => 'required|numeric|min:0.000001',
        ]);

        $currencyCode = strtoupper(trim($validated['currency_code']));
        $currency = Currency::firstOrCreate(
            ['code' => $currencyCode],
            ['name' => $currencyCode, 'symbol' => $currencyCode]
        );

        Country::create([
            'name' => $validated['name'],
            'iso_code' => strtoupper(trim($validated['iso_code'])),
            'currency_id' => $currency->id,
            'currency_code' => $currencyCode,
            'exchange_rate_to_usd' => $validated['exchange_rate_to_usd'],
        ]);

        return redirect()->route('settings.countries.index')
            ->with('success', 'Country saved successfully.');
    }

    public function updateCountry(Request $request, Country $country)
    {
        $user = Auth::user();
        if ((int) ($user->business_id ?? 0) !== 1) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:10|unique:countries,iso_code,' . $country->id,
            'currency_code' => 'required|string|max:10',
            'exchange_rate_to_usd' => 'required|numeric|min:0.000001',
        ]);

        $currencyCode = strtoupper(trim($validated['currency_code']));
        $currency = Currency::firstOrCreate(
            ['code' => $currencyCode],
            ['name' => $currencyCode, 'symbol' => $currencyCode]
        );

        $country->update([
            'name' => $validated['name'],
            'iso_code' => strtoupper(trim($validated['iso_code'])),
            'currency_id' => $currency->id,
            'currency_code' => $currencyCode,
            'exchange_rate_to_usd' => $validated['exchange_rate_to_usd'],
        ]);

        return redirect()->route('settings.countries.index')
            ->with('success', 'Country updated successfully.');
    }

    public function destroyCountry(Country $country)
    {
        $user = Auth::user();
        if ((int) ($user->business_id ?? 0) !== 1) {
            abort(403);
        }

        $country->delete();

        return redirect()->route('settings.countries.index')
            ->with('success', 'Country deleted successfully.');
    }

    private function currencyOptions(): array
    {
        $savedCodes = Currency::query()
            ->orderBy('code')
            ->pluck('code')
            ->map(fn ($code) => strtoupper((string) $code))
            ->toArray();

        $defaults = ['UGX', 'USD', 'KES', 'TZS', 'RWF'];

        return array_values(array_unique(array_merge($savedCodes, $defaults)));
    }

    private function countryOptions(): array
    {
        return [
            ['name' => 'Uganda', 'iso_code' => 'UG', 'currency_code' => 'UGX'],
            ['name' => 'Kenya', 'iso_code' => 'KE', 'currency_code' => 'KES'],
            ['name' => 'Tanzania', 'iso_code' => 'TZ', 'currency_code' => 'TZS'],
            ['name' => 'Rwanda', 'iso_code' => 'RW', 'currency_code' => 'RWF'],
            ['name' => 'South Sudan', 'iso_code' => 'SS', 'currency_code' => 'SSP'],
            ['name' => 'United States', 'iso_code' => 'US', 'currency_code' => 'USD'],
        ];
    }
}
