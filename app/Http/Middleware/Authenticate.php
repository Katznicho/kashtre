<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Check the request path to determine which login page to redirect to
        if ($request->is('third-party-payer-dashboard*')) {
            // Log authentication check for debugging
            $guard = Auth::guard('third_party_payer');
            $userId = $guard->id();
            $user = $userId ? $guard->user() : null;
            
            Log::info('Authenticate middleware - third-party payer dashboard', [
                'path' => $request->path(),
                'guard_check' => $guard->check() ? 'yes' : 'no',
                'user_id' => $userId,
                'user_loaded' => $user ? 'yes' : 'no',
                'session_key' => 'login_third_party_payer_' . hash('sha256', 'third_party_payer'),
                'session_data' => $request->session()->all(),
            ]);
            return route('third-party-payer.login');
        }
        
        if ($request->is('cashier-dashboard*')) {
            return route('cashier.login');
        }

        return route('login');
    }
}
