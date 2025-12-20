<?php

namespace App\Http\Controllers\ThirdPartyPayerAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('third-party-payer-auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        // Attempt to authenticate using the third_party_payer guard
        if (Auth::guard('third_party_payer')->attempt($credentials, $request->filled('remember'))) {
            $account = Auth::guard('third_party_payer')->user();
            
            // Check if account is active
            if (!$account->isActive()) {
                Auth::guard('third_party_payer')->logout();
                throw ValidationException::withMessages([
                    'username' => ['Your account has been deactivated. Please contact support.'],
                ]);
            }

            // Update last login
            $account->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            return redirect()->intended(route('third-party-payer-dashboard.index'));
        }

        throw ValidationException::withMessages([
            'username' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::guard('third_party_payer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('third-party-payer.login');
    }
}
