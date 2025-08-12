<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\MagicLinkLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MagicLinkController extends Controller
{
    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        // Generate a secure token
        $token = Str::random(64);
        
        // Store the token in the database with expiration
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Create the login URL
        $loginUrl = URL::temporarySignedRoute(
            'login.magic-link.verify',
            now()->addMinutes(15),
            ['email' => $request->email, 'token' => $token]
        );

        // Send the email
        try {
            Mail::to($user->email)->send(new MagicLinkLogin($user, $loginUrl));
            
            return back()->with('status', 'Login link sent! Please check your email.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send login link. Please try again.']);
        }
    }

    public function verifyMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid login link.']);
        }

        // Get the stored token
        $storedToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$storedToken || !Hash::check($request->token, $storedToken->token)) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired login link.']);
        }

        // Check if token is expired (15 minutes)
        if (Carbon::parse($storedToken->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('login')->withErrors(['email' => 'Login link has expired. Please request a new one.']);
        }

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log the user in
        auth()->login($user);

        return redirect()->intended(route('dashboard'))->with('status', 'Welcome back!');
    }
}
