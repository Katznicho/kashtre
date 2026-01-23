<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page with tabs
     */
    public function index(Request $request)
    {
        // Check if user has permission
        if (!in_array('View Insurance Companies', Auth::user()->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view settings.');
        }

        $activeTab = $request->get('tab', 'insurance-companies');
        
        $insuranceCompanies = \App\Models\InsuranceCompany::with('business')
            ->latest()
            ->paginate(15);

        return view('settings.index', compact('activeTab', 'insuranceCompanies'));
    }
}
