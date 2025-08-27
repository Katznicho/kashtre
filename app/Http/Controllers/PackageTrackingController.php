<?php

namespace App\Http\Controllers;

use App\Models\PackageTracking;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageTrackingController extends Controller
{
    /**
     * Display a listing of package tracking records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PackageTracking::with(['client', 'invoice', 'packageItem', 'includedItem'])
            ->where('business_id', $user->business_id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id !== '') {
            $query->where('client_id', $request->client_id);
        }

        // Filter by package item
        if ($request->has('package_item_id') && $request->package_item_id !== '') {
            $query->where('package_item_id', $request->package_item_id);
        }

        // Search by client name or invoice number
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('client', function($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('invoice', function($invoiceQuery) use ($search) {
                    $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                });
            });
        }

        $packageTrackings = $query->with(['client', 'invoice', 'packageItem.packageItems.includedItem'])->orderBy('created_at', 'desc')->paginate(20);
        $clients = Client::where('business_id', $user->business_id)->get();

        return view('package-tracking.index', compact('packageTrackings', 'clients'));
    }



    /**
     * Display the specified package tracking record
     */
    public function show(PackageTracking $packageTracking)
    {
        $user = Auth::user();
        
        // Check if user has access to this package tracking record
        if ($packageTracking->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package tracking record.');
        }

        $packageTracking->load(['client', 'invoice', 'packageItem', 'includedItem']);
        
        // Load all included items for this package
        $packageItems = $packageTracking->packageItem->packageItems()->with('includedItem')->get();

        return view('package-tracking.show', compact('packageTracking', 'packageItems'));
    }

    /**
     * Show the form for editing the specified package tracking record
     */
    public function edit(PackageTracking $packageTracking)
    {
        $user = Auth::user();
        
        // Check if user has access to this package tracking record
        if ($packageTracking->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package tracking record.');
        }

        $clients = Client::where('business_id', $user->business_id)->get();
        $invoices = Invoice::where('business_id', $user->business_id)->get();

        return view('package-tracking.edit', compact('packageTracking', 'clients', 'invoices'));
    }

    /**
     * Update the specified package tracking record
     */
    public function update(Request $request, PackageTracking $packageTracking)
    {
        $user = Auth::user();
        
        // Check if user has access to this package tracking record
        if ($packageTracking->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package tracking record.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'required|exists:invoices,id',
            'package_item_id' => 'required|exists:items,id',
            'included_item_id' => 'required|exists:items,id',
            'total_quantity' => 'required|integer|min:1',
            'used_quantity' => 'required|integer|min:0',
            'remaining_quantity' => 'required|integer|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'package_price' => 'required|numeric|min:0',
            'item_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,fully_used,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Validate that used_quantity + remaining_quantity = total_quantity
        if ($validated['used_quantity'] + $validated['remaining_quantity'] !== $validated['total_quantity']) {
            return back()->withErrors(['used_quantity' => 'Used quantity + remaining quantity must equal total quantity.']);
        }

        $packageTracking->update($validated);

        return redirect()->route('package-tracking.index')
            ->with('success', 'Package tracking record updated successfully.');
    }

    /**
     * Remove the specified package tracking record
     */
    public function destroy(PackageTracking $packageTracking)
    {
        $user = Auth::user();
        
        // Check if user has access to this package tracking record
        if ($packageTracking->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package tracking record.');
        }

        $packageTracking->delete();

        return redirect()->route('package-tracking.index')
            ->with('success', 'Package tracking record deleted successfully.');
    }

    /**
     * Use package quantity (mark as used)
     */
    public function useQuantity(Request $request, PackageTracking $packageTracking)
    {
        $user = Auth::user();
        
        // Check if user has access to this package tracking record
        if ($packageTracking->business_id !== $user->business_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package tracking record.'
            ], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = $validated['quantity'];

        if ($packageTracking->remaining_quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient remaining quantity. Available: ' . $packageTracking->remaining_quantity
            ], 400);
        }

        $packageTracking->useQuantity($quantity);

        return response()->json([
            'success' => true,
            'message' => 'Package quantity used successfully.',
            'remaining_quantity' => $packageTracking->remaining_quantity,
            'used_quantity' => $packageTracking->used_quantity,
            'status' => $packageTracking->status
        ]);
    }

    /**
     * Dashboard for package tracking
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get package tracking statistics
        $totalPackages = PackageTracking::where('business_id', $user->business_id)->count();
        $activePackages = PackageTracking::where('business_id', $user->business_id)
            ->where('status', 'active')
            ->count();
        $expiredPackages = PackageTracking::where('business_id', $user->business_id)
            ->where('status', 'expired')
            ->count();
        $fullyUsedPackages = PackageTracking::where('business_id', $user->business_id)
            ->where('status', 'fully_used')
            ->count();

        // Get recent package tracking records
        $recentPackages = PackageTracking::with(['client', 'packageItem.packageItems.includedItem', 'includedItem'])
            ->where('business_id', $user->business_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get packages expiring soon (within 30 days)
        $expiringSoon = PackageTracking::with(['client', 'packageItem.packageItems.includedItem', 'includedItem'])
            ->where('business_id', $user->business_id)
            ->where('status', 'active')
            ->where('valid_until', '<=', now()->addDays(30))
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until', 'asc')
            ->limit(10)
            ->get();

        // Get packages with low remaining quantity (less than 25% remaining)
        $lowQuantity = PackageTracking::with(['client', 'packageItem.packageItems.includedItem', 'includedItem'])
            ->where('business_id', $user->business_id)
            ->where('status', 'active')
            ->whereRaw('(remaining_quantity / total_quantity) < 0.25')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('remaining_quantity', 'asc')
            ->limit(10)
            ->get();

        return view('package-tracking.dashboard', compact(
            'totalPackages',
            'activePackages',
            'expiredPackages',
            'fullyUsedPackages',
            'recentPackages',
            'expiringSoon',
            'lowQuantity'
        ));
    }

    /**
     * Get package tracking records for a specific client
     */
    public function clientPackages(Client $client)
    {
        $user = Auth::user();
        
        // Check if user has access to this client
        if ($client->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to client.');
        }

        $packageTrackings = PackageTracking::with(['invoice', 'packageItem', 'includedItem'])
            ->where('business_id', $user->business_id)
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('package-tracking.client-packages', compact('packageTrackings', 'client'));
    }
}
