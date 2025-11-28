<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Business;
use App\Models\Branch;
use App\Models\MaturationPeriod;
use App\Models\ThirdPartyPayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;
        
        // Check if current branch exists
        if (!$currentBranch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned. Please contact administrator.');
        }
        
        // Get the requested branch or use current branch
        $selectedBranchId = $request->get('branch_id', $currentBranch->id);
        
        // Check if user has access to the selected branch
        $allowedBranches = (array) ($user->allowed_branches ?? []);
        if (!in_array($selectedBranchId, $allowedBranches)) {
            $selectedBranchId = $currentBranch->id;
        }
        
        $selectedBranch = Branch::find($selectedBranchId) ?? $currentBranch;
        
        // For Kashtre (business_id == 1), show all clients from all businesses
        if ($business->id == 1) {
            $clients = Client::where('business_id', '!=', 1)
                ->with(['business', 'branch'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            // Get today's clients count for all businesses
            $todayClients = Client::where('business_id', '!=', 1)
                ->whereDate('created_at', today())
                ->count();
        } else {
            // Get clients for the selected business and branch
            $clients = Client::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            // Get today's clients count for the selected branch
            $todayClients = Client::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereDate('created_at', today())
                ->count();
        }
            
        // Get all branches the user has access to for the filter
        $availableBranches = Branch::whereIn('id', $allowedBranches)->get();
            
        return view('clients.index', compact('clients', 'todayClients', 'business', 'currentBranch', 'selectedBranch', 'availableBranches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;
        
        // Check if current branch exists
        if (!$currentBranch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned. Please contact administrator.');
        }
        
        // Get available payment methods from maturation periods for this business
        $availablePaymentMethods = MaturationPeriod::where('business_id', $business->id)
            ->where('is_active', true)
            ->get()
            ->pluck('payment_method')
            ->unique()
            ->values()
            ->toArray();
        
        // Define the order for payment methods
        $paymentMethodOrder = [
            'mobile_money' => 1,
            'v_card' => 2,
            'p_card' => 3,
            'bank_transfer' => 4,
            'cash' => 5,
        ];
        
        // Sort payment methods according to the defined order
        // Methods not in the order list will come after (with higher priority number)
        usort($availablePaymentMethods, function ($a, $b) use ($paymentMethodOrder) {
            $orderA = $paymentMethodOrder[$a] ?? 999;
            $orderB = $paymentMethodOrder[$b] ?? 999;
            
            if ($orderA === $orderB) {
                // If both have the same priority (or both not in list), maintain original order
                return 0;
            }
            
            return $orderA <=> $orderB;
        });
        
        // Payment method display names
        $paymentMethodNames = [
            'insurance' => '🛡️ Insurance',
            'credit_arrangement' => '💳 Credit Arrangement',
            'mobile_money' => '📱 MM (Mobile Money)',
            'v_card' => '💳 V Card (Virtual Card)',
            'p_card' => '💳 P Card (Physical Card)',
            'bank_transfer' => '🏦 Bank Transfer',
            'cash' => '💵 Cash',
        ];
        
        return view('clients.create', compact('business', 'currentBranch', 'availablePaymentMethods', 'paymentMethodNames'));
    }

    /**
     * Search for existing client by surname, first name, and date of birth
     */
    public function searchExistingClient(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;

        $request->validate([
            'surname' => 'required|string',
            'first_name' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        // Search for existing client with matching surname, first_name, and date_of_birth
        $existingClient = Client::where('business_id', $business->id)
            ->where('branch_id', $currentBranch->id)
            ->where('surname', $request->surname)
            ->where('first_name', $request->first_name)
            ->where('date_of_birth', $request->date_of_birth)
            ->first();

        if ($existingClient) {
            return response()->json([
                'found' => true,
                'client' => [
                    'id' => $existingClient->id,
                    'client_id' => $existingClient->client_id,
                    'other_names' => $existingClient->other_names,
                    'nin' => $existingClient->nin,
                    'tin_number' => $existingClient->tin_number,
                    'sex' => $existingClient->sex,
                    'marital_status' => $existingClient->marital_status,
                    'occupation' => $existingClient->occupation,
                    'phone_number' => $existingClient->phone_number,
                    'village' => $existingClient->village,
                    'county' => $existingClient->county,
                    'email' => $existingClient->email,
                    'services_category' => $existingClient->services_category,
                    'payment_methods' => $existingClient->payment_methods,
                    'payment_phone_number' => $existingClient->payment_phone_number,
                    'nok_surname' => $existingClient->nok_surname,
                    'nok_first_name' => $existingClient->nok_first_name,
                    'nok_other_names' => $existingClient->nok_other_names,
                    'nok_sex' => $existingClient->nok_sex,
                    'nok_marital_status' => $existingClient->nok_marital_status,
                    'nok_occupation' => $existingClient->nok_occupation,
                    'nok_phone_number' => $existingClient->nok_phone_number,
                    'nok_village' => $existingClient->nok_village,
                    'nok_county' => $existingClient->nok_county,
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;
        
        // Check if current branch exists
        if (!$currentBranch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned. Please contact administrator.');
        }
        
        $clientType = $request->input('client_type', 'individual');
        
        // Handle different client types
        switch ($clientType) {
            case 'individual':
                return $this->storeIndividual($request, $business, $currentBranch);
            case 'company':
                return $this->storeCompany($request, $business, $currentBranch);
            case 'walk_in':
                return $this->storeWalkIn($request, $business, $currentBranch);
            default:
                return redirect()->route('clients.create')
                    ->with('error', 'Invalid client type.')
                    ->withInput();
        }
    }
    
    /**
     * Store an individual repeat customer
     */
    private function storeIndividual(Request $request, $business, $currentBranch)
    {
        // Check if client already exists with same surname, first_name, and date_of_birth
        $existingClient = Client::where('business_id', $business->id)
            ->where('branch_id', $currentBranch->id)
            ->where('surname', $request->surname)
            ->where('first_name', $request->first_name)
            ->where('date_of_birth', $request->date_of_birth)
            ->first();

        // If existing client found, redirect to POS with that client (no new record needed)
        if ($existingClient) {
            return redirect()->route('pos.item-selection', $existingClient)
                ->with('success', 'Existing client found! Redirecting to ordering page. Client ID: ' . $existingClient->client_id);
        }

        // Validate NIN for new clients
        $ninValidation = 'nullable|string|max:255';
        
        // Get available payment methods from maturation periods for this business
        $availablePaymentMethods = MaturationPeriod::where('business_id', $business->id)
            ->where('is_active', true)
            ->pluck('payment_method')
            ->unique()
            ->values()
            ->toArray();
        
        // Validate payment methods - check if business has any set up
        if (empty($availablePaymentMethods)) {
            return redirect()->route('clients.create')
                ->with('error', 'No payment methods have been set up for your business. Please contact the administrator to configure payment methods in Maturation Periods.')
                ->withInput();
        }
        
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'nin' => $ninValidation,
            'tin_number' => 'nullable|string|max:255',
            'sex' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'occupation' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'village' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'services_category' => 'required|in:dental,optical,outpatient,inpatient,maternity,funeral',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'required|string|in:' . implode(',', $availablePaymentMethods),
            'payment_phone_number' => 'nullable|string|max:255',
            
            // Next of Kin details
            'nok_surname' => 'required|string|max:255',
            'nok_first_name' => 'required|string|max:255',
            'nok_other_names' => 'nullable|string|max:255',
            'nok_sex' => 'required|in:male,female,other',
            'nok_marital_status' => 'required|in:single,married,divorced,widowed',
            'nok_occupation' => 'required|string|max:255',
            'nok_phone_number' => 'required|string|max:255',
            'nok_village' => 'required|string|max:255',
            'nok_county' => 'required|string|max:255',
            'is_credit_eligible' => 'nullable|boolean',
            'is_long_stay' => 'nullable|boolean',
            'max_credit' => 'nullable|numeric|min:0',
        ]);
        
        // Generate new client_id and visit_id for new client
        $clientId = Client::generateClientId(
            $business,
            $validated['surname'] ?? '',
            $validated['first_name'] ?? '',
            $validated['date_of_birth'] ?? null
        );
        $isCreditEligible = $validated['is_credit_eligible'] ?? false;
        $isLongStay = $validated['is_long_stay'] ?? false;
        $visitId = Client::generateVisitId($business, $currentBranch, $isCreditEligible, $isLongStay);
        
        // Set visit expiration: null for long-stay (never expires until discharged), otherwise tomorrow
        $visitExpiresAt = $isLongStay ? null : \Carbon\Carbon::tomorrow()->startOfDay();
        
        // Generate full name by concatenating the name fields
        $fullName = trim($validated['surname'] . ' ' . $validated['first_name'] . ' ' . ($validated['other_names'] ?? ''));
        
        // Create the client
        $client = Client::create([
            'uuid' => Str::uuid(),
            'business_id' => $business->id,
            'branch_id' => $currentBranch->id,
            'client_id' => $clientId,
            'visit_id' => $visitId,
            'visit_expires_at' => $visitExpiresAt,
            'name' => $fullName,
            'surname' => $validated['surname'],
            'first_name' => $validated['first_name'],
            'other_names' => $validated['other_names'],
            'nin' => $validated['nin'],
            'tin_number' => $validated['tin_number'],
            'sex' => $validated['sex'],
            'date_of_birth' => $validated['date_of_birth'],
            'marital_status' => $validated['marital_status'],
            'occupation' => $validated['occupation'],
            'phone_number' => $validated['phone_number'],
            'village' => $validated['village'],
            'county' => $validated['county'],
            'email' => $validated['email'],
            'services_category' => $validated['services_category'],
            'payment_methods' => $validated['payment_methods'] ?? [],
            'payment_phone_number' => $validated['payment_phone_number'],
            'nok_surname' => $validated['nok_surname'],
            'nok_first_name' => $validated['nok_first_name'],
            'nok_other_names' => $validated['nok_other_names'],
            'nok_sex' => $validated['nok_sex'],
            'nok_marital_status' => $validated['nok_marital_status'],
            'nok_occupation' => $validated['nok_occupation'],
            'nok_phone_number' => $validated['nok_phone_number'],
            'nok_village' => $validated['nok_village'],
            'nok_county' => $validated['nok_county'],
            'balance' => 0,
            'status' => 'active',
            'client_type' => 'individual',
            'is_credit_eligible' => $isCreditEligible,
            'is_long_stay' => $isLongStay,
            'max_credit' => $isCreditEligible ? ($validated['max_credit'] ?? $business->max_first_party_credit_limit) : null,
        ]);
        
        return redirect()->route('pos.item-selection', $client)
            ->with('success', 'Client registered successfully! Client ID: ' . $clientId);
    }
    
    /**
     * Store a company client
     */
    private function storeCompany(Request $request, $business, $currentBranch)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_tin' => 'required|string|max:255|unique:clients,tin_number',
            'company_phone' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_address' => 'required|string',
            'company_contact_person' => 'required|string|max:255',
            'register_type' => 'required|in:client_only,client_and_payer',
        ]);
        
        // Generate client_id for company (using company name)
        $clientId = Client::generateClientId(
            $business,
            $validated['company_name'],
            '',
            null
        );
        
        $visitId = Client::generateVisitId($business, $currentBranch, false, false);
        $visitExpiresAt = \Carbon\Carbon::tomorrow()->startOfDay();
        
        // Create the company client
        $client = Client::create([
            'uuid' => Str::uuid(),
            'business_id' => $business->id,
            'branch_id' => $currentBranch->id,
            'client_id' => $clientId,
            'visit_id' => $visitId,
            'visit_expires_at' => $visitExpiresAt,
            'name' => $validated['company_name'],
            'surname' => $validated['company_name'],
            'first_name' => '',
            'tin_number' => $validated['company_tin'],
            'phone_number' => $validated['company_phone'],
            'email' => $validated['company_email'],
            'occupation' => $validated['company_contact_person'],
            'balance' => 0,
            'status' => 'active',
            'client_type' => 'company',
        ]);
        
        $message = 'Company client registered successfully! Client ID: ' . $clientId;
        
        // Create the company as a third party payer if requested
        if ($validated['register_type'] === 'client_and_payer') {
            ThirdPartyPayer::create([
                'business_id' => $business->id,
                'type' => 'normal_client',
                'client_id' => $client->id,
                'name' => $validated['company_name'],
                'contact_person' => $validated['company_contact_person'],
                'phone_number' => $validated['company_phone'],
                'email' => $validated['company_email'],
                'address' => $validated['company_address'],
                'credit_limit' => 0, // Will be set up later
                'status' => 'active',
            ]);
            
            $message = 'Company client registered successfully as a client and third party payer! Client ID: ' . $clientId;
        }
        
        return redirect()->route('clients.show', $client)
            ->with('success', $message);
    }
    
    /**
     * Store a walk-in client (minimal information)
     */
    private function storeWalkIn(Request $request, $business, $currentBranch)
    {
        // Generate client_id using the standard format (business prefix + 7-char code)
        // For walk-in, we use "WalkIn" as surname and "Client" as first name
        $clientId = Client::generateClientId(
            $business,
            'WalkIn',
            'Client',
            null
        );
        
        $visitId = Client::generateVisitId($business, $currentBranch, false, false);
        $visitExpiresAt = \Carbon\Carbon::tomorrow()->startOfDay();
        
        // Create minimal walk-in client
        $client = Client::create([
            'uuid' => Str::uuid(),
            'business_id' => $business->id,
            'branch_id' => $currentBranch->id,
            'client_id' => $clientId,
            'visit_id' => $visitId,
            'visit_expires_at' => $visitExpiresAt,
            'name' => 'Walk In Client',
            'surname' => 'Walk In',
            'first_name' => 'Client',
            'phone_number' => '0000000000', // Placeholder for walk-in clients
            'email' => 'walkin@example.com', // Placeholder email
            'balance' => 0,
            'status' => 'active',
            'client_type' => 'walk_in',
        ]);
        
        return redirect()->route('pos.item-selection', $client)
            ->with('success', 'Walk-in client created! Client ID: ' . $clientId);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            abort(403, 'Unauthorized access to client.');
        }
        
        return view('clients.show', compact('client', 'business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            abort(403, 'Unauthorized access to client.');
        }
        
        return view('clients.edit', compact('client', 'business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            abort(403, 'Unauthorized access to client.');
        }
        
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'nin' => 'nullable|string|max:255',
            'id_passport_no' => 'nullable|string|max:255',
            'sex' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'occupation' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'services_category' => 'nullable|in:dental,optical,outpatient,inpatient,maternity,funeral',
            'preferred_payment_method' => 'nullable|in:cash,bank_transfer,credit_card,insurance,postpaid,mobile_money',
            'status' => 'required|in:active,inactive,suspended',
            'is_credit_eligible' => 'nullable|boolean',
            'is_long_stay' => 'nullable|boolean',
            'max_credit' => 'nullable|numeric|min:0',
            
            // Next of Kin details
            'nok_surname' => 'nullable|string|max:255',
            'nok_first_name' => 'nullable|string|max:255',
            'nok_other_names' => 'nullable|string|max:255',
            'nok_marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nok_occupation' => 'nullable|string|max:255',
            'nok_phone_number' => 'nullable|string|max:255',
            'nok_physical_address' => 'nullable|string|max:500',
        ]);
        
        // Check if credit or long-stay flags changed - if so, regenerate visit ID
        $needsVisitIdRegeneration = false;
        $isCreditEligible = isset($validated['is_credit_eligible']) ? (bool)$validated['is_credit_eligible'] : $client->is_credit_eligible;
        
        if (isset($validated['is_credit_eligible']) && $validated['is_credit_eligible'] != $client->is_credit_eligible) {
            $needsVisitIdRegeneration = true;
        }
        if (isset($validated['is_long_stay']) && $validated['is_long_stay'] != $client->is_long_stay) {
            $needsVisitIdRegeneration = true;
        }
        
        // Handle max_credit: only set if credit eligible, otherwise null
        if (isset($validated['max_credit'])) {
            $validated['max_credit'] = $isCreditEligible ? ($validated['max_credit'] ?? $business->max_first_party_credit_limit) : null;
        } elseif (!$isCreditEligible) {
            $validated['max_credit'] = null;
        }
        
        $client->update($validated);
        
        // Regenerate visit ID if flags changed
        if ($needsVisitIdRegeneration) {
            $business = $client->business ?: Business::find($client->business_id);
            $branch = $client->branch ?: Branch::find($client->branch_id);
            if ($business && $branch) {
                $newVisitId = Client::generateVisitId(
                    $business, 
                    $branch, 
                    $client->is_credit_eligible ?? false, 
                    $client->is_long_stay ?? false
                );
                $client->visit_id = $newVisitId;
                
                // If long-stay, set expiration to null (never expires until discharged)
                // Otherwise, set to tomorrow
                if ($client->is_long_stay) {
                    $client->visit_expires_at = null;
                } else {
                    $client->visit_expires_at = \Carbon\Carbon::tomorrow()->startOfDay();
                }
                $client->save();
            }
        }
        
        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            abort(403, 'Unauthorized access to client.');
        }
        
        $client->delete();
        
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully!');
    }

    /**
     * Update payment methods for a client
     */
    public function updatePaymentMethods(Request $request, Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to client.'
            ], 403);
        }
        
        $validated = $request->validate([
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'string|in:packages,insurance,credit_arrangement_institutions,deposits_account_balance,mobile_money,v_card,p_card,bank_transfer,cash'
        ]);
        
        $client->update([
            'payment_methods' => $validated['payment_methods']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment methods updated successfully!',
            'payment_methods' => $client->payment_methods
        ]);
    }

    /**
     * Update payment phone number for a client
     */
    public function updatePaymentPhone(Request $request, Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to client.'
            ], 403);
        }
        
        $validated = $request->validate([
            'payment_phone_number' => 'nullable|string|max:255'
        ]);
        
        $client->update([
            'payment_phone_number' => $validated['payment_phone_number']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment phone number updated successfully!',
            'payment_phone_number' => $client->payment_phone_number
        ]);
    }

    /**
     * Admit a client - enable credit and/or long-stay
     */
    public function admit(Request $request, Client $client)
    {
        \Illuminate\Support\Facades\Log::info("🚀 ========== ADMISSION PROCESS STARTED ==========", [
            'timestamp' => now()->toDateTimeString(),
            'client_id' => $client->id,
            'client_name' => $client->name,
            'client_client_id' => $client->client_id,
            'client_visit_id' => $client->visit_id,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'Unknown',
            'request_data' => $request->all()
        ]);
        
        $user = Auth::user();
        $business = $user->business;
        
        \Illuminate\Support\Facades\Log::info("📋 ADMISSION: User and Business Information", [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_permissions' => $user->permissions ?? [],
            'business_id' => $business->id ?? null,
            'business_name' => $business->name ?? null,
            'client_business_id' => $client->business_id
        ]);
        
        // Check permission
        if (!in_array('Admit Clients', $user->permissions ?? [])) {
            \Illuminate\Support\Facades\Log::warning("❌ ADMISSION: Permission Denied", [
                'user_id' => $user->id,
                'user_permissions' => $user->permissions ?? [],
                'required_permission' => 'Admit Clients'
            ]);
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'You do not have permission to admit clients.');
        }
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            \Illuminate\Support\Facades\Log::warning("❌ ADMISSION: Unauthorized Access Attempt", [
                'user_business_id' => $user->business_id,
                'client_business_id' => $client->business_id,
                'user_id' => $user->id,
                'client_id' => $client->id
            ]);
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Unauthorized access to client.');
        }

        // Check if client already has /M suffix (long-stay)
        if ($client->is_long_stay || preg_match('/\/M$/', $client->visit_id)) {
            \Illuminate\Support\Facades\Log::warning("❌ ADMISSION: Client Already Admitted", [
                'client_id' => $client->id,
                'client_visit_id' => $client->visit_id,
                'is_long_stay' => $client->is_long_stay,
                'visit_id_has_m_suffix' => preg_match('/\/M$/', $client->visit_id)
            ]);
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Client is already admitted. Please discharge first.');
        }

        \Illuminate\Support\Facades\Log::info("✅ ADMISSION: Permission and Access Checks Passed", [
            'client_id' => $client->id,
            'user_id' => $user->id
        ]);

        $validated = $request->validate([
            'enable_credit' => 'boolean',
            'enable_long_stay' => 'boolean',
            'max_credit' => 'nullable|numeric|min:0',
            'queue_item_id' => 'nullable|integer|exists:service_delivery_queues,id',
        ]);

        \Illuminate\Support\Facades\Log::info("✅ ADMISSION: Request Validation Passed", [
            'validated_data' => $validated,
            'request_has_enable_credit' => $request->has('enable_credit'),
            'request_has_enable_long_stay' => $request->has('enable_long_stay'),
            'request_max_credit' => $request->get('max_credit')
        ]);

        // Use explicit values from request if provided, otherwise use business settings
        $enableCredit = $request->has('enable_credit') 
            ? (bool)$validated['enable_credit'] 
            : ($business->admit_enable_credit ?? false);
        
        $enableLongStay = $request->has('enable_long_stay') 
            ? (bool)$validated['enable_long_stay'] 
            : ($business->admit_enable_long_stay ?? false);

        \Illuminate\Support\Facades\Log::info("⚙️ ADMISSION: Business Settings Retrieved", [
            'business_id' => $business->id,
            'business_admit_enable_credit' => $business->admit_enable_credit ?? false,
            'business_admit_enable_long_stay' => $business->admit_enable_long_stay ?? false,
            'business_max_first_party_credit_limit' => $business->max_first_party_credit_limit ?? 0,
            'final_enable_credit' => $enableCredit,
            'final_enable_long_stay' => $enableLongStay
        ]);

        if (!$enableCredit && !$enableLongStay) {
            \Illuminate\Support\Facades\Log::warning("❌ ADMISSION: No Options Selected", [
                'enable_credit' => $enableCredit,
                'enable_long_stay' => $enableLongStay
            ]);
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Please select at least one option: Credit or Long-Stay.');
        }

        \Illuminate\Support\Facades\Log::info("📝 ADMISSION: Client Status Before Update", [
            'client_id' => $client->id,
            'current_is_credit_eligible' => $client->is_credit_eligible,
            'current_is_long_stay' => $client->is_long_stay,
            'current_max_credit' => $client->max_credit,
            'current_visit_id' => $client->visit_id,
            'current_visit_expires_at' => $client->visit_expires_at
        ]);

        // Update client flags based on what was explicitly selected during admission
        // This ensures the visit ID format matches the selected options
        $client->is_credit_eligible = $enableCredit;
        $client->is_long_stay = $enableLongStay;

        // Set max_credit if credit is enabled
        if ($enableCredit) {
            // Use provided max_credit or default to business first party credit limit
            $client->max_credit = $validated['max_credit'] ?? $business->max_first_party_credit_limit;
            \Illuminate\Support\Facades\Log::info("💳 ADMISSION: Credit Limit Set", [
                'max_credit' => $client->max_credit,
                'source' => $request->has('max_credit') ? 'request' : 'business_default'
            ]);
        } else {
            // Clear max_credit if credit is not enabled
            $client->max_credit = null;
            \Illuminate\Support\Facades\Log::info("💳 ADMISSION: Credit Limit Cleared (Credit Not Enabled)");
        }

        // Regenerate visit ID with new suffix
        $branch = $client->branch ?: Branch::find($client->branch_id);
        if ($business && $branch) {
            $oldVisitId = $client->visit_id;
            $client->visit_id = Client::generateVisitId($business, $branch, $client->is_credit_eligible, $client->is_long_stay);
            
            \Illuminate\Support\Facades\Log::info("🆔 ADMISSION: Visit ID Regenerated", [
                'old_visit_id' => $oldVisitId,
                'new_visit_id' => $client->visit_id,
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'is_credit_eligible' => $client->is_credit_eligible,
                'is_long_stay' => $client->is_long_stay
            ]);
            
            // Set visit_expires_at to null for long-stay clients
            if ($client->is_long_stay) {
                $oldVisitExpiresAt = $client->visit_expires_at;
                $client->visit_expires_at = null;
                \Illuminate\Support\Facades\Log::info("📅 ADMISSION: Visit Expiry Cleared (Long-Stay)", [
                    'old_visit_expires_at' => $oldVisitExpiresAt
                ]);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning("⚠️ ADMISSION: Could Not Regenerate Visit ID", [
                'business_exists' => !is_null($business),
                'branch_exists' => !is_null($branch),
                'branch_id' => $client->branch_id
            ]);
        }

        $client->save();
        
        \Illuminate\Support\Facades\Log::info("💾 ADMISSION: Client Record Saved", [
            'client_id' => $client->id,
            'updated_is_credit_eligible' => $client->is_credit_eligible,
            'updated_is_long_stay' => $client->is_long_stay,
            'updated_max_credit' => $client->max_credit,
            'updated_visit_id' => $client->visit_id,
            'updated_visit_expires_at' => $client->visit_expires_at
        ]);
        
        // If this is an AJAX request (for per-item admission), return JSON and skip item processing
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit ID updated successfully.',
                'visit_id' => $client->visit_id,
                'is_credit_eligible' => $client->is_credit_eligible,
                'is_long_stay' => $client->is_long_stay
            ]);
        }

        // Process admission items - mark them as completed and move money
        // This applies whether credit is enabled or not - admission always marks items as completed
        try {
            $moneyTrackingService = new \App\Services\MoneyTrackingService();
            
            // Try to extract service point ID from redirect URL (e.g., /service-points/68/client/25/details)
            $redirectTo = $request->get('redirect_to', '');
            $servicePointIdFromUrl = null;
            if (preg_match('/service-points\/(\d+)\//', $redirectTo, $matches)) {
                $servicePointIdFromUrl = (int)$matches[1];
                \Illuminate\Support\Facades\Log::info("🔍 ADMISSION: Extracted Service Point ID from URL", [
                    'redirect_url' => $redirectTo,
                    'service_point_id' => $servicePointIdFromUrl
                ]);
            }
            
            // Find service points to process
            // Priority: 1) Service point from URL, 2) Service points named "admission"
            $admissionServicePointIds = [];
            
            if ($servicePointIdFromUrl) {
                // Use the service point from the URL
                $servicePointFromUrl = \App\Models\ServicePoint::find($servicePointIdFromUrl);
                if ($servicePointFromUrl) {
                    $admissionServicePointIds[] = $servicePointFromUrl->id;
                    \Illuminate\Support\Facades\Log::info("📍 ADMISSION: Using Service Point from URL", [
                        'service_point_id' => $servicePointFromUrl->id,
                        'service_point_name' => $servicePointFromUrl->name
                    ]);
                }
            }
            
            // Also find ALL admission service points (case-insensitive match)
            // This handles cases where there might be multiple service points with "admission" in the name
            $admissionServicePoints = \App\Models\ServicePoint::whereRaw('LOWER(TRIM(name)) = ?', ['admission'])->get();
            
            // Add admission service points to the list (avoid duplicates)
            foreach ($admissionServicePoints as $sp) {
                if (!in_array($sp->id, $admissionServicePointIds)) {
                    $admissionServicePointIds[] = $sp->id;
                }
            }
            
            if (empty($admissionServicePointIds)) {
                \Illuminate\Support\Facades\Log::warning("⚠️ ADMISSION: No Admission Service Points Found", [
                    'client_id' => $client->id,
                    'service_point_id_from_url' => $servicePointIdFromUrl
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info("📍 ADMISSION: Service Points to Process", [
                    'service_point_ids' => $admissionServicePointIds,
                    'service_point_id_from_url' => $servicePointIdFromUrl,
                    'count' => count($admissionServicePointIds)
                ]);
            }
            
            // Find queued items to process
            // If queue_item_id is provided, process only that specific item
            // Otherwise, process all items at the identified service points
            $queueItemId = $request->get('queue_item_id');
            
            if ($queueItemId) {
                // Process only the specific queue item
                $queuedItemsAtAdmission = \App\Models\ServiceDeliveryQueue::where('id', $queueItemId)
                    ->where('client_id', $client->id)
                    ->whereIn('status', ['pending', 'partially_done'])
                    ->with(['invoice', 'item', 'servicePoint'])
                    ->get();
                
                \Illuminate\Support\Facades\Log::info("🎯 ADMISSION: Processing Specific Queue Item", [
                    'queue_item_id' => $queueItemId,
                    'client_id' => $client->id,
                    'found' => $queuedItemsAtAdmission->count() > 0
                ]);
            } else {
                // Find all queued items at the identified service points for this client
                $queuedItemsAtAdmission = \App\Models\ServiceDeliveryQueue::where('client_id', $client->id)
                    ->whereIn('status', ['pending', 'partially_done'])
                    ->when(!empty($admissionServicePointIds), function($query) use ($admissionServicePointIds) {
                        $query->whereIn('service_point_id', $admissionServicePointIds);
                    })
                    ->with(['invoice', 'item', 'servicePoint'])
                    ->get();
            }
            
            \Illuminate\Support\Facades\Log::info("🎯 ADMISSION: Found Queued Items at Service Points", [
                'client_id' => $client->id,
                'service_point_ids' => $admissionServicePointIds,
                'queued_items_count' => $queuedItemsAtAdmission->count(),
                'items' => $queuedItemsAtAdmission->map(function($item) {
                    return [
                        'queue_id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->name ?? 'Unknown',
                        'status' => $item->status,
                        'invoice_id' => $item->invoice_id,
                        'service_point_id' => $item->service_point_id,
                        'service_point_name' => $item->servicePoint->name ?? 'Unknown'
                    ];
                })->toArray()
            ]);
            
            // Get unique invoice IDs
            $invoiceIds = $queuedItemsAtAdmission->pluck('invoice_id')->unique()->filter();
            
            // Get invoices
            $pendingInvoices = \App\Models\Invoice::whereIn('id', $invoiceIds)
                ->where('status', '!=', 'cancelled')
                ->with('items')
                ->get();
            
            // Process money movements for all admission items (no credit check - money movements are the same for everyone)
            \Illuminate\Support\Facades\Log::info("=== ADMISSION: PROCESSING SUSPENSE MOVEMENTS FOR PENDING INVOICES ===", [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'pending_invoices_count' => $pendingInvoices->count()
            ]);
            
            foreach ($pendingInvoices as $invoice) {
                    \Illuminate\Support\Facades\Log::info("📄 ADMISSION: Processing Invoice", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_status' => $invoice->status,
                        'invoice_total_amount' => $invoice->total_amount,
                        'invoice_service_charge' => $invoice->service_charge ?? 0,
                        'invoice_items_count' => $invoice->items ? count($invoice->items) : 0
                    ]);
                    
                    // Get items from service delivery queue (pending items at admission service point)
                    // IMPORTANT: Only process items at the admission service point for this invoice
                    $queuedItems = $queuedItemsAtAdmission->where('invoice_id', $invoice->id);
                    
                    \Illuminate\Support\Facades\Log::info("🎯 ADMISSION: Filtering Items for Service Points", [
                        'invoice_id' => $invoice->id,
                        'service_point_ids' => $admissionServicePointIds,
                        'queued_items_count' => $queuedItems->count(),
                        'queued_items_details' => $queuedItems->map(function($item) {
                            return [
                                'queue_id' => $item->id,
                                'item_id' => $item->item_id,
                                'item_name' => $item->item->name ?? 'Unknown',
                                'service_point_id' => $item->service_point_id,
                                'service_point_name' => $item->servicePoint->name ?? 'Unknown',
                                'status' => $item->status
                            ];
                        })->toArray()
                    ]);
                    
                    \Illuminate\Support\Facades\Log::info("📦 ADMISSION: Found Queued Items", [
                        'invoice_id' => $invoice->id,
                        'queued_items_count' => $queuedItems->count(),
                        'queued_items_details' => $queuedItems->map(function($item) {
                            return [
                                'queue_id' => $item->id,
                                'item_id' => $item->item_id,
                                'item_name' => $item->item->name ?? 'Unknown',
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->price * $item->quantity,
                                'status' => $item->status,
                                'service_point_id' => $item->service_point_id
                            ];
                        })->toArray()
                    ]);
                    
                    if ($queuedItems->isEmpty()) {
                        \Illuminate\Support\Facades\Log::info("⏭️ ADMISSION: Skipping Invoice - No Queued Items", [
                            'invoice_id' => $invoice->id
                        ]);
                        continue;
                    }
                    
                    // Prepare item data for processSaveAndExit (same format as when marking items as completed)
                    $itemDataArray = $queuedItems->map(function($queuedItem) {
                        return [
                            'item_id' => $queuedItem->item_id,
                            'id' => $queuedItem->item_id,
                            'quantity' => $queuedItem->quantity,
                            'price' => $queuedItem->price,
                            'total_amount' => $queuedItem->price * $queuedItem->quantity
                        ];
                    })->toArray();
                    
                    \Illuminate\Support\Facades\Log::info("📊 ADMISSION: Prepared Item Data for Money Movement", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'queued_items_count' => $queuedItems->count(),
                        'item_data_array' => $itemDataArray,
                        'total_items_amount' => array_sum(array_column($itemDataArray, 'total_amount'))
                    ]);
                    
                    // First, ensure suspense account movements are processed (money TO suspense accounts)
                    // This is critical - suspense movements must exist before we can move money from suspense to final
                    $invoiceItems = collect($invoice->items ?? [])->map(function($item) {
                        return [
                            'item_id' => $item['id'] ?? $item['item_id'] ?? null,
                            'id' => $item['id'] ?? $item['item_id'] ?? null,
                            'name' => $item['name'] ?? $item['item_name'] ?? null,
                            'displayName' => $item['displayName'] ?? $item['name'] ?? $item['item_name'] ?? null,
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => $item['price'] ?? $item['default_price'] ?? 0,
                            'total_amount' => ($item['price'] ?? $item['default_price'] ?? 0) * ($item['quantity'] ?? 1)
                        ];
                    })->filter(function($item) {
                        // Exclude deposit items
                        $name = strtolower(trim($item['name'] ?? $item['displayName'] ?? ''));
                        return $name !== 'deposit';
                    })->values()->toArray();
                    
                    if (!empty($invoiceItems)) {
                        // Check if suspense movements already processed for this invoice
                        // We need transfers with the correct transfer_type for credit clients
                        $hasSuspenseMovements = \App\Models\MoneyTransfer::where('invoice_id', $invoice->id)
                            ->whereIn('transfer_type', ['suspense_movement', 'credit_suspense_movement', 'service_charge', 'credit_service_charge'])
                            ->exists();
                        
                        if (!$hasSuspenseMovements) {
                            \Illuminate\Support\Facades\Log::info("=== ADMISSION: PROCESSING SUSPENSE MOVEMENTS FOR INVOICE ===", [
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number,
                                'items_count' => count($invoiceItems),
                                'client_is_credit_eligible' => $client->is_credit_eligible
                            ]);
                            
                            // Process suspense account movements for this invoice
                            // This will create transfers with credit_suspense_movement type for credit clients
                            $suspenseMovements = $moneyTrackingService->processSuspenseAccountMovements($invoice, $invoiceItems);
                            
                            \Illuminate\Support\Facades\Log::info("=== ADMISSION: SUSPENSE MOVEMENTS PROCESSED ===", [
                                'invoice_id' => $invoice->id,
                                'movements_count' => count($suspenseMovements),
                                'movements' => $suspenseMovements
                            ]);
                        } else {
                            \Illuminate\Support\Facades\Log::info("=== ADMISSION: SUSPENSE MOVEMENTS ALREADY EXIST ===", [
                                'invoice_id' => $invoice->id,
                                'note' => 'Suspense movements already processed, proceeding to suspense-to-final movement'
                            ]);
                        }
                        
                        // Refresh suspense account balances to ensure they're up to date
                        $generalSuspense = $moneyTrackingService->getOrCreateGeneralSuspenseAccount($invoice->business, $client->id);
                        $packageSuspense = $moneyTrackingService->getOrCreatePackageSuspenseAccount($invoice->business, $client->id);
                        $kashtreSuspense = $moneyTrackingService->getOrCreateKashtreSuspenseAccount($invoice->business, $client->id);
                        
                        \Illuminate\Support\Facades\Log::info("=== ADMISSION: SUSPENSE ACCOUNT BALANCES BEFORE FINAL MOVEMENT ===", [
                            'invoice_id' => $invoice->id,
                            'general_suspense_balance' => $generalSuspense->fresh()->balance,
                            'package_suspense_balance' => $packageSuspense->fresh()->balance,
                            'kashtre_suspense_balance' => $kashtreSuspense->fresh()->balance,
                            'total_suspense_balance' => $generalSuspense->fresh()->balance + $packageSuspense->fresh()->balance + $kashtreSuspense->fresh()->balance
                        ]);
                    }
                    
                    // Then, process suspense to final money movement (same as when marking items as completed)
                    // This moves money FROM suspense accounts TO final accounts
                    \Illuminate\Support\Facades\Log::info("=== ADMISSION: PROCESSING SUSPENSE TO FINAL MONEY MOVEMENT ===", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'items_count' => count($itemDataArray),
                        'item_status' => 'completed' // Treat admission as completed for money movement
                    ]);
                    
                    $transferRecords = $moneyTrackingService->processSaveAndExit($invoice, $itemDataArray, 'completed');
                    
                    \Illuminate\Support\Facades\Log::info("✅ ADMISSION: Money Movements Completed", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'transfer_records_count' => count($transferRecords['transfer_records'] ?? []),
                        'transfer_records' => $transferRecords['transfer_records'] ?? []
                    ]);
                    
                    // Mark all queued items as completed (same as clicking "Completed" button)
                    $itemsMarkedCount = 0;
                    foreach ($queuedItems as $queuedItem) {
                        if ($queuedItem->status !== 'completed') {
                            \Illuminate\Support\Facades\Log::info("✅ ADMISSION: Marking Item as Completed", [
                                'queue_id' => $queuedItem->id,
                                'item_id' => $queuedItem->item_id,
                                'item_name' => $queuedItem->item->name ?? 'Unknown',
                                'invoice_id' => $invoice->id,
                                'previous_status' => $queuedItem->status,
                                'marked_by_user_id' => $user->id
                            ]);
                            
                            $queuedItem->markAsCompleted($user->id);
                            $itemsMarkedCount++;
                        } else {
                            \Illuminate\Support\Facades\Log::info("ℹ️ ADMISSION: Item Already Completed", [
                                'queue_id' => $queuedItem->id,
                                'item_id' => $queuedItem->item_id,
                                'invoice_id' => $invoice->id
                            ]);
                        }
                    }
                    
                    \Illuminate\Support\Facades\Log::info("✅ ADMISSION: All Items Processed for Invoice", [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total_queued_items' => $queuedItems->count(),
                        'items_marked_as_completed' => $itemsMarkedCount,
                        'items_already_completed' => $queuedItems->count() - $itemsMarkedCount
                    ]);
                }
                
            \Illuminate\Support\Facades\Log::info("✅ ADMISSION: All Invoices Processed Successfully", [
                'client_id' => $client->id,
                'total_invoices_processed' => $pendingInvoices->count()
            ]);
            
            // IMPORTANT: Mark all admission items as completed
            // This ensures items disappear from the queue after admission
            $itemsMarkedCount = 0;
            foreach ($queuedItemsAtAdmission as $queuedItem) {
                if ($queuedItem->status !== 'completed') {
                    \Illuminate\Support\Facades\Log::info("✅ ADMISSION: Marking Admission Item as Completed", [
                        'queue_id' => $queuedItem->id,
                        'item_id' => $queuedItem->item_id,
                        'item_name' => $queuedItem->item->name ?? 'Unknown',
                        'previous_status' => $queuedItem->status,
                        'marked_by_user_id' => $user->id
                    ]);
                    
                    $queuedItem->markAsCompleted($user->id);
                    $itemsMarkedCount++;
                }
            }
            
            \Illuminate\Support\Facades\Log::info("✅ ADMISSION: All Admission Items Marked as Completed", [
                'client_id' => $client->id,
                'total_admission_items' => $queuedItemsAtAdmission->count(),
                'items_marked_as_completed' => $itemsMarkedCount,
                'items_already_completed' => $queuedItemsAtAdmission->count() - $itemsMarkedCount
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("❌ ADMISSION: Error Processing Admission", [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            // Don't fail admission if processing fails
        }

        $message = 'Client admitted successfully.';
        if ($enableCredit && $enableLongStay) {
            $message .= ' Credit and Long-Stay enabled.';
        } elseif ($enableCredit) {
            $message .= ' Credit enabled.';
        } elseif ($enableLongStay) {
            $message .= ' Long-Stay enabled.';
        }

        \Illuminate\Support\Facades\Log::info("🎉 ========== ADMISSION PROCESS COMPLETED SUCCESSFULLY ==========", [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'client_client_id' => $client->client_id,
            'client_visit_id' => $client->visit_id,
            'is_credit_eligible' => $client->is_credit_eligible,
            'is_long_stay' => $client->is_long_stay,
            'max_credit' => $client->max_credit,
            'success_message' => $message,
            'redirect_to' => $request->get('redirect_to', route('clients.show', $client))
        ]);

        // Redirect back to the page they came from, or default to client show page
        $redirectTo = $request->get('redirect_to', route('clients.show', $client));
        return redirect($redirectTo)
            ->with('success', $message);
    }

    /**
     * Discharge a client - remove long-stay status
     */
    public function discharge(Request $request, Client $client)
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Check permission
        if (!in_array('Discharge Clients', $user->permissions ?? [])) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'You do not have permission to discharge clients.');
        }
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Unauthorized access to client.');
        }

        // Check if client has /M suffix (long-stay)
        if (!$client->is_long_stay && !preg_match('/\/M$/', $client->visit_id)) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Client is not admitted (no long-stay status).');
        }

        // Determine what to remove based on business settings
        $removeLongStay = $business->discharge_remove_long_stay ?? true; // Always true by default
        $removeCredit = $business->discharge_remove_credit ?? false;
        
        // Remove long-stay flag if configured
        if ($removeLongStay) {
            $client->is_long_stay = false;
        }
        
        // Remove credit eligibility if configured
        if ($removeCredit) {
            $client->is_credit_eligible = false;
            $client->max_credit = null;
        }
        
        // Determine final credit and long-stay states for visit ID generation
        $finalCreditEligible = $removeCredit ? false : ($client->is_credit_eligible ?? false);
        $finalLongStay = $removeLongStay ? false : ($client->is_long_stay ?? false);

        // Regenerate visit ID based on final states
        $branch = $client->branch ?: Branch::find($client->branch_id);
        if ($business && $branch) {
            $client->visit_id = Client::generateVisitId($business, $branch, $finalCreditEligible, $finalLongStay);
            
            // Set visit_expires_at to tomorrow for non-long-stay clients
            if ($finalLongStay) {
                $client->visit_expires_at = null;
            } else {
                $client->visit_expires_at = \Carbon\Carbon::tomorrow()->startOfDay();
            }
        }

        $client->save();

        // Build success message
        $message = 'Client discharged successfully.';
        $changes = [];
        if ($removeLongStay) {
            $changes[] = 'Long-stay removed';
        }
        if ($removeCredit) {
            $changes[] = 'Credit services removed';
        }
        if (!empty($changes)) {
            $message .= ' ' . implode(', ', $changes) . '.';
        }
        $message .= ' Visit ID is now available for reissuance.';

        // Redirect back to the page they came from, or default to client show page
        $redirectTo = $request->get('redirect_to', route('clients.show', $client));
        return redirect($redirectTo)
            ->with('success', $message);
    }
}
