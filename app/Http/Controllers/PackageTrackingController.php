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
        // The Livewire component handles all the data fetching and filtering
        return view('package-tracking.index');
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

        $packageTracking->load(['client', 'invoice', 'packageItem', 'trackingItems.includedItem']);
        
        // Get tracking items for this package
        $trackingItems = $packageTracking->trackingItems()->with('includedItem')->get();

        // Load package sales for this package tracking record
        $packageSales = \App\Models\PackageSales::where('package_tracking_id', $packageTracking->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('package-tracking.show', compact('packageTracking', 'trackingItems', 'packageSales'));
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
     * Use package item quantity (mark as used)
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
            'tracking_item_id' => 'required|exists:package_tracking_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $trackingItem = $packageTracking->trackingItems()->find($validated['tracking_item_id']);
        
        if (!$trackingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking item not found for this package.'
            ], 404);
        }

        $quantity = $validated['quantity'];

        if ($trackingItem->remaining_quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient remaining quantity. Available: ' . $trackingItem->remaining_quantity
            ], 400);
        }

        $trackingItem->useQuantity($quantity);

        return response()->json([
            'success' => true,
            'message' => 'Package item quantity used successfully.',
            'remaining_quantity' => $trackingItem->remaining_quantity,
            'used_quantity' => $trackingItem->used_quantity,
            'package_status' => $packageTracking->fresh()->status
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
        $recentPackages = PackageTracking::with(['client', 'packageItem', 'trackingItems.includedItem'])
            ->where('business_id', $user->business_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get packages expiring soon (within 30 days)
        $expiringSoon = PackageTracking::with(['client', 'packageItem', 'trackingItems.includedItem'])
            ->where('business_id', $user->business_id)
            ->where('status', 'active')
            ->where('valid_until', '<=', now()->addDays(30))
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until', 'asc')
            ->limit(10)
            ->get();

        // Get packages with low remaining quantity (less than 25% remaining)
        $lowQuantity = PackageTracking::with(['client', 'packageItem', 'trackingItems.includedItem'])
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

        $packageTrackings = PackageTracking::with(['invoice', 'packageItem', 'trackingItems.includedItem'])
            ->where('business_id', $user->business_id)
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('package-tracking.client-packages', compact('packageTrackings', 'client'));
    }
}
