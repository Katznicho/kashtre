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

        // Manually find the user by username (case-insensitive)
        $account = \App\Models\ThirdPartyPayerAccount::whereRaw('LOWER(username) = ?', [strtolower($request->username)])
            ->where('status', 'active')
            ->first();

        // Debug logging
        \Log::info('Third-party payer login attempt', [
            'username' => $request->username,
            'account_found' => $account ? 'yes' : 'no',
            'account_id' => $account ? $account->id : null,
            'account_status' => $account ? $account->status : null,
        ]);

        // Check if account exists
        if (!$account) {
            \Log::warning('Third-party payer login failed: Account not found', [
                'username' => $request->username,
            ]);
            throw ValidationException::withMessages([
                'username' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Get the raw password hash from database (bypass mutator)
        $passwordHash = $account->getAttributes()['password'] ?? $account->password;

        // Check if password is correct
        $passwordMatches = \Illuminate\Support\Facades\Hash::check($request->password, $passwordHash);

        \Log::info('Third-party payer password check', [
            'account_id' => $account->id,
            'password_matches' => $passwordMatches ? 'yes' : 'no',
        ]);

        if ($passwordMatches) {
            // Check if account is active
            if (!$account->isActive()) {
                \Log::warning('Third-party payer login failed: Account inactive', [
                    'account_id' => $account->id,
                    'status' => $account->status,
                ]);
                throw ValidationException::withMessages([
                    'username' => ['Your account has been deactivated. Please contact support.'],
                ]);
            }

            // Update last login
            $account->update(['last_login_at' => now()]);

            // Log the user in (Laravel handles session security automatically)
            Auth::guard('third_party_payer')->login($account, $request->filled('remember'));

            // Ensure session is saved
            $request->session()->save();

            \Log::info('Third-party payer login successful', [
                'account_id' => $account->id,
                'username' => $account->username,
                'authenticated_check' => Auth::guard('third_party_payer')->check() ? 'yes' : 'no',
                'user_id' => Auth::guard('third_party_payer')->id(),
                'session_id' => $request->session()->getId(),
                'session_data' => $request->session()->all(),
            ]);

            return redirect()->intended(route('third-party-payer-dashboard.index'));
        }

        \Log::warning('Third-party payer login failed: Invalid password', [
            'account_id' => $account->id,
            'username' => $account->username,
        ]);

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
