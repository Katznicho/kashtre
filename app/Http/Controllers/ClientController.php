<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Business;
use App\Models\Branch;
use App\Models\MaturationPeriod;
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
        
        // Check if client already exists with same surname, first_name, and date_of_birth
        $existingClient = Client::where('business_id', $business->id)
            ->where('branch_id', $currentBranch->id)
            ->where('surname', $request->surname)
            ->where('first_name', $request->first_name)
            ->where('date_of_birth', $request->date_of_birth)
            ->first();

        // If existing client found, redirect to POS with that client (no new record needed)
        if ($existingClient) {
            // Don't regenerate visit_id if it was cleared/expired - only generate when creating invoice
            // $existingClient->ensureActiveVisitId();

            return redirect()->route('pos.item-selection', $existingClient)
                ->with('success', 'Existing client found! Redirecting to ordering page. Client ID: ' . $existingClient->client_id);
        }

        // Validate NIN for new clients
        $ninValidation = 'nullable|string|max:255|unique:clients,nin';
        
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
            'is_credit_eligible' => $isCreditEligible,
            'is_long_stay' => $isLongStay,
            'max_credit' => $isCreditEligible ? ($validated['max_credit'] ?? $business->max_first_party_credit_limit) : null,
        ]);
        
        return redirect()->route('pos.item-selection', $client)
            ->with('success', 'Client registered successfully! Client ID: ' . $clientId);
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
            'nin' => 'nullable|string|max:255|unique:clients,nin,' . $client->id,
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
        $user = Auth::user();
        $business = $user->business;
        
        // Check permission
        if (!in_array('Admit Clients', $user->permissions ?? [])) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'You do not have permission to admit clients.');
        }
        
        // Check if user has access to this client
        if ($user->business_id !== 1 && $client->business_id !== $business->id) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Unauthorized access to client.');
        }

        // Check if client already has /M suffix (long-stay)
        if ($client->is_long_stay || preg_match('/\/M$/', $client->visit_id)) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Client is already admitted. Please discharge first.');
        }

        $validated = $request->validate([
            'enable_credit' => 'boolean',
            'enable_long_stay' => 'boolean',
            'max_credit' => 'nullable|numeric|min:0',
        ]);

        $enableCredit = $request->has('enable_credit') ? (bool)$validated['enable_credit'] : false;
        $enableLongStay = $request->has('enable_long_stay') ? (bool)$validated['enable_long_stay'] : false;

        if (!$enableCredit && !$enableLongStay) {
            $redirectTo = $request->get('redirect_to', route('clients.show', $client));
            return redirect($redirectTo)
                ->with('error', 'Please select at least one option: Credit or Long-Stay.');
        }

        // Update client flags based on what was explicitly selected during admission
        // This ensures the visit ID format matches the selected options
        $client->is_credit_eligible = $enableCredit;
        $client->is_long_stay = $enableLongStay;

        // Set max_credit if credit is enabled
        if ($enableCredit) {
            // Use provided max_credit or default to business first party credit limit
            $client->max_credit = $validated['max_credit'] ?? $business->max_first_party_credit_limit;
        } else {
            // Clear max_credit if credit is not enabled
            $client->max_credit = null;
        }

        // Regenerate visit ID with new suffix
        $branch = $client->branch ?: Branch::find($client->branch_id);
        if ($business && $branch) {
            $client->visit_id = Client::generateVisitId($business, $branch, $client->is_credit_eligible, $client->is_long_stay);
            
            // Set visit_expires_at to null for long-stay clients
            if ($client->is_long_stay) {
                $client->visit_expires_at = null;
            }
        }

        $client->save();

        $message = 'Client admitted successfully.';
        if ($enableCredit && $enableLongStay) {
            $message .= ' Credit and Long-Stay enabled.';
        } elseif ($enableCredit) {
            $message .= ' Credit enabled.';
        } elseif ($enableLongStay) {
            $message .= ' Long-Stay enabled.';
        }

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

        // Remove long-stay flag
        $client->is_long_stay = false;

        // Regenerate visit ID without /M suffix (keep /C if credit eligible)
        $branch = $client->branch ?: Branch::find($client->branch_id);
        if ($business && $branch) {
            $client->visit_id = Client::generateVisitId($business, $branch, $client->is_credit_eligible, false);
            
            // Set visit_expires_at to tomorrow for non-long-stay clients
            $client->visit_expires_at = \Carbon\Carbon::tomorrow()->startOfDay();
        }

        $client->save();

        // Redirect back to the page they came from, or default to client show page
        $redirectTo = $request->get('redirect_to', route('clients.show', $client));
        return redirect($redirectTo)
            ->with('success', 'Client discharged successfully. Visit ID is now available for reissuance.');
    }
}
