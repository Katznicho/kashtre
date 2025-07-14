<?php
// app/Http/Controllers/ApiKeyController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendApiCredentials;

class ApiKeyController extends Controller
{
    public function index()
    {
        $apiKey = ApiKey::where('business_id', Auth::user()->business_id)->first();

        return view('developers.api-keys', compact('apiKey'));
    }

    public function generate()
    {
        $user = Auth::user();

        // Delete any old keys for this business
        ApiKey::where('business_id', $user->business_id)->delete();

        // Generate new key & secret
        $key = 'marz_' . Str::random(16);
        $secret = Str::random(32);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'business_id' => $user->business_id,
            'key' => $key,
            'secret' => $secret,
        ]);

        $encoded = base64_encode("$key:$secret");

        return redirect()->back()->with([
            'success' => 'New API credentials generated!',
            'encoded' => $encoded,
            'key' => $key,
            'secret' => $secret,
        ]);
    }



public function email(Request $request)
{
    try {
        $businessId = auth()->user()->business_id;
        $apiKey = ApiKey::where('business_id', $businessId)->first();

        if (!$apiKey) {
            return back()->with('error', 'You have no active API key. Please generate one.');
        }

        $key = $apiKey->key;
        $secret = $apiKey->secret;
        $encoded = base64_encode("$key:$secret");

        Mail::to(auth()->user()->email)->send(new SendApiCredentials($key, $secret, $encoded));

        return back()->with('success', 'API credentials emailed to your registered email address.');
    } catch (\Throwable $e) {
        return back()->with('error', 'Error sending email: ' . $e->getMessage());
    }
}

}
