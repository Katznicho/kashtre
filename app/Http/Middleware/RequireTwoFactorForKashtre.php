<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorForKashtre
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Only enforce 2FA for Kashtre users (business_id == 1)
        if ($user->business_id != 1) {
            return $next($request);
        }

        // Allow access to these routes to avoid redirect loops
        $allowedRoutes = [
            'profile.show',
            'profile.update',
            'logout',
            'login',
            'register',
        ];

        // Check if current route is allowed (handle case where route might be null)
        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $allowedRoutes)) {
            return $next($request);
        }

        // Allow access to two-factor authentication routes, password confirmation, and profile-related routes
        $allowedPaths = [
            'user/two-factor-authentication',
            'user/confirmed-two-factor-authentication',
            'user/confirm-password',
            'user/recovery-codes',
        ];
        
        foreach ($allowedPaths as $path) {
            if ($request->is($path) || $request->is($path . '*')) {
                return $next($request);
            }
        }
        
        // Also allow password confirmation route name
        if ($routeName === 'password.confirm') {
            return $next($request);
        }

        // Check if 2FA is enabled (two_factor_confirmed_at is set)
        if (empty($user->two_factor_confirmed_at)) {
            return redirect()->route('profile.show')
                ->with('warning', 'Two-factor authentication (2FA) is required for all Kashtre users. You must enable 2FA before accessing other parts of the system. Please set up 2FA in your profile settings below.');
        }

        return $next($request);
    }
}

