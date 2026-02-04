<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ThirdPartyApiService;
use Illuminate\Support\Facades\Log;

class ThirdPartyVendorsController extends Controller
{
    protected $apiService;

    public function __construct(ThirdPartyApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display a listing of connected third party vendors
     */
    public function index()
    {
        $business = auth()->user()->business;
        
        if (!$business) {
            return redirect()->route('dashboard')->with('error', 'No business associated with your account.');
        }

        try {
            // Get connected vendors from third-party API
            $baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get("{$baseUrl}/api/v1/businesses/{$business->id}/connected-vendors");

            $vendors = [];
            
            if ($response->successful()) {
                $data = $response->json();
                $vendors = $data['data'] ?? [];
            } else {
                Log::warning('Failed to fetch connected vendors', [
                    'business_id' => $business->id,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return view('third-party-vendors.index', compact('vendors', 'business'));
        } catch (\Exception $e) {
            Log::error('Exception while fetching connected vendors', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return view('third-party-vendors.index', [
                'vendors' => [],
                'business' => $business,
                'error' => 'Failed to load connected vendors. Please try again later.',
            ]);
        }
    }
}
