<?php

namespace App\Http\Controllers;

use App\Models\PackageSales;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageSalesController extends Controller
{
    /**
     * Display a listing of package sales records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Calculate summary statistics
        $totalSales = PackageSales::where('business_id', $user->business_id)->sum('amount');
        $totalQuantity = PackageSales::where('business_id', $user->business_id)->sum('qty');
        $totalRecords = PackageSales::where('business_id', $user->business_id)->count();

        return view('package-sales.index', compact('totalSales', 'totalQuantity', 'totalRecords'));
    }

    /**
     * Display the specified package sales record
     */
    public function show(PackageSales $packageSale)
    {
        $user = Auth::user();
        
        // Check if user has access to this package sales record
        if ($packageSale->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package sales record.');
        }

        $packageSale->load(['client', 'packageTracking', 'item', 'business', 'branch']);
        
        return view('package-sales.show', compact('packageSale'));
    }

    /**
     * Show package sales history with advanced filtering
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $query = PackageSales::with(['client', 'packageTracking', 'item'])
            ->where('business_id', $user->business_id);

        // Apply filters
        if ($request->has('client_id') && $request->client_id !== '') {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $packageSales = $query->orderBy('date', 'desc')->paginate(50);
        $clients = Client::where('business_id', $user->business_id)->get();

        // Get summary statistics
        $summary = [
            'total_amount' => $query->sum('amount'),
            'total_quantity' => $query->sum('qty'),
            'total_records' => $query->count(),
            'average_amount' => $query->avg('amount'),
        ];

        return view('package-sales.history', compact('packageSales', 'clients', 'summary'));
    }

    /**
     * Export package sales data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = PackageSales::with(['client', 'packageTracking', 'item'])
            ->where('business_id', $user->business_id);

        // Apply same filters as history
        if ($request->has('client_id') && $request->client_id !== '') {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $packageSales = $query->orderBy('date', 'desc')->get();

        // Generate CSV
        $filename = 'package_sales_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($packageSales) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Client Name',
                'Invoice Number',
                'Package Tracking Number (PKN)',
                'Item Name',
                'Quantity',
                'Amount',
                'Status',
                'Notes'
            ]);

            // CSV data
            foreach ($packageSales as $sale) {
                fputcsv($file, [
                    $sale->date->format('Y-m-d'),
                    $sale->name,
                    $sale->invoice_number,
                    $sale->pkn,
                    $sale->item_name,
                    $sale->qty,
                    $sale->amount,
                    $sale->status,
                    $sale->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Remove the specified package sales record
     */
    public function destroy(PackageSales $packageSale)
    {
        $user = Auth::user();
        
        // Check if user has access to this package sales record
        if ($packageSale->business_id !== $user->business_id) {
            abort(403, 'Unauthorized access to package sales record.');
        }

        $packageSale->delete();

        return redirect()->route('package-sales.index')
            ->with('success', 'Package sales record deleted successfully.');
    }

    /**
     * Get package sales statistics for dashboard
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();
        $query = PackageSales::where('business_id', $user->business_id);

        // Apply date filter if provided
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('date', '<=', $request->end_date);
        }

        $stats = [
            'total_sales' => $query->sum('amount'),
            'total_quantity' => $query->sum('qty'),
            'total_records' => $query->count(),
            'average_amount' => $query->avg('amount'),
            'top_clients' => $query->select('client_id', 'name', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('client_id', 'name')
                ->orderBy('total_amount', 'desc')
                ->limit(5)
                ->get(),
            'top_items' => $query->select('item_name', DB::raw('SUM(qty) as total_quantity'), DB::raw('SUM(amount) as total_amount'))
                ->groupBy('item_name')
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
