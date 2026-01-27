<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use App\Services\ThirdPartyApiService;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InsuranceCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('settings.index', ['tab' => 'insurance-companies']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('insurance-company.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Company Information
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tin' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'head_office_address' => 'nullable|string|max:500',
            'postal_address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            
            // User Account Information
            'user_email' => 'required|email|max:255',
            'user_username' => 'required|string|max:255',
        ]);

        try {
            // Always auto-generate 8-character alphanumeric code (not editable)
            // Keep generating until we get a unique one
            $maxAttempts = 20;
            $attempts = 0;
            $code = null;
            $thirdPartyService = app(ThirdPartyApiService::class);
            
            do {
                // Generate 8-character alphanumeric code (uppercase letters and numbers)
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = '';
                for ($i = 0; $i < 8; $i++) {
                    $code .= $characters[rand(0, strlen($characters) - 1)];
                }
                $attempts++;
                
                // Check if code exists in local database first (faster)
                $existsLocally = InsuranceCompany::where('code', $code)->exists();
                
                if (!$existsLocally) {
                    // Check if code exists in third-party system
                    $existing = $thirdPartyService->getInsuranceCompanyByCode($code);
                    
                    if ($existing === null) {
                        // Code doesn't exist in either system, we can use it
                        break;
                    }
                }
                
                if ($attempts >= $maxAttempts) {
                    Log::error('Failed to generate unique third party vendor code after multiple attempts', [
                        'attempts' => $attempts,
                    ]);
                    return back()->withInput()->withErrors([
                        'error' => 'Unable to generate a unique company code. Please try again.'
                    ]);
                }
            } while (true);
            
            $validated['code'] = $code;
            
            Log::info('Auto-generated third party vendor code', [
                'code' => $code,
                'attempts' => $attempts,
            ]);

            // Check for duplicates in third-party system
            $thirdPartyService = app(ThirdPartyApiService::class);
            
            $existingBusiness = $thirdPartyService->checkBusinessExists($validated['name'], $validated['email']);
            if ($existingBusiness) {
                    return back()->withInput()->withErrors([
                        'email' => 'This third party vendor already exists in the third-party system.'
                    ]);
            }

            $existingUser = $thirdPartyService->checkUserExists($validated['user_email'], $validated['user_username']);
            if ($existingUser) {
                return back()->withInput()->withErrors([
                    'user_email' => 'This user email or username already exists in the third-party system.'
                ]);
            }

            // Create insurance company in Kashtre
            $insuranceCompany = InsuranceCompany::create([
                'business_id' => Auth::user()->business_id,
                'name' => $validated['name'],
                'code' => $validated['code'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'tin' => $validated['tin'] ?? null,
                'address' => $validated['address'] ?? null,
                'head_office_address' => $validated['head_office_address'] ?? $validated['address'] ?? null,
                'postal_address' => $validated['postal_address'] ?? null,
                'website' => $validated['website'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Register with third-party API
            $businessData = [
                'name' => $insuranceCompany->name,
                'code' => $insuranceCompany->code,
                'email' => $insuranceCompany->email,
                'phone' => $insuranceCompany->phone,
                'tin' => $insuranceCompany->tin,
                'address' => $insuranceCompany->address,
                'head_office_address' => $insuranceCompany->head_office_address,
                'postal_address' => $insuranceCompany->postal_address,
                'website' => $insuranceCompany->website,
                'description' => $insuranceCompany->description,
            ];

            // Use default password for now
            $defaultPassword = 'password';
            
            // Auto-generate user name from company name (Admin for [Company Name])
            $userName = 'Admin for ' . $validated['name'];
            
            $userData = [
                'name' => $userName,
                'email' => $validated['user_email'],
                'username' => $validated['user_username'],
                'password' => $defaultPassword,
            ];

            $thirdPartyResponse = $thirdPartyService->registerBusinessAndUser($businessData, $userData);

            if ($thirdPartyResponse) {
                // Store third-party IDs
                $insuranceCompany->update([
                    'third_party_business_id' => $thirdPartyResponse['business']['id'] ?? null,
                    'third_party_user_id' => $thirdPartyResponse['user']['id'] ?? null,
                    'third_party_username' => $validated['user_username'],
                ]);

                // Generate password reset token and send email (handled by third-party system)
                $resetTokenResponse = null;
                $emailMessage = '';
                
                try {
                    $resetTokenResponse = $thirdPartyService->generatePasswordResetToken($validated['user_email']);
                    
                    if ($resetTokenResponse && ($resetTokenResponse['success'] ?? false)) {
                        Log::info('Password reset email sent by third-party system', [
                            'email' => $validated['user_email'],
                        ]);
                        $emailMessage = ' A password reset email has been sent to ' . $validated['user_email'] . ' from the third-party system.';
                    } else {
                        Log::warning('Password reset email failed', [
                            'email' => $validated['user_email'],
                            'response' => $resetTokenResponse,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to request password reset email from third-party system', [
                        'email' => $validated['user_email'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue anyway - registration was successful
                }
                
                return redirect()->route('settings.index', ['tab' => 'insurance-companies'])
                    ->with('success', 'Third party vendor created and registered successfully!' . $emailMessage);
            } else {
                return back()->withInput()->withErrors([
                    'error' => 'Failed to register with third-party system. Please try again.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create third party vendor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InsuranceCompany $insuranceCompany)
    {
        $insuranceCompany->load('business');
        return view('insurance-company.show', compact('insuranceCompany'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InsuranceCompany $insuranceCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsuranceCompany $insuranceCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsuranceCompany $insuranceCompany)
    {
        //
    }
}
