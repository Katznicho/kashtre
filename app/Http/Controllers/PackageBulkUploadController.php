<?php

namespace App\Http\Controllers;

use App\Exports\PackageBulkTemplateExport;
use App\Imports\PackageBulkTemplateImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Business;
use App\Models\Item;

class PackageBulkUploadController extends Controller
{
    /**
     * Show the bulk upload form for packages and bulk items
     */
    public function index()
    {
        // Get businesses based on user permissions
        if (Auth::user()->business_id == 1) {
            $businesses = Business::where('id', '!=', 1)->get();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
        }

        return view('items.package-bulk-upload', compact('businesses'));
    }

    /**
     * Download the template for packages and bulk items
     */
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        // Validate business access
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $request->business_id) {
            return redirect()->back()->with('error', 'Unauthorized access to business data');
        }

        try {
            $business = Business::find($request->business_id);
            $filename = 'package_bulk_template_' . str_replace(' ', '_', $business->name) . '.xlsx';
            
            // Log for debugging
            Log::info('Downloading package/bulk template for business: ' . $business->name);
            
            return Excel::download(new PackageBulkTemplateExport($request->business_id), $filename);
            
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading template: ' . $e->getMessage());
        }
    }

    /**
     * Import the uploaded file for packages and bulk items
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'business_id' => 'required|exists:businesses,id',
        ]);

        // Validate business access
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $request->business_id) {
            return redirect()->back()->with('error', 'Unauthorized access to business data');
        }

        try {
            Log::info("=== STARTING PACKAGE/BULK IMPORT ===");
            $import = new PackageBulkTemplateImport($request->business_id);
            
            Log::info("=== CALLING Excel::import() ===");
            Excel::import($import, $request->file('file'));
            Log::info("=== Excel::import() COMPLETED ===");

            // Create branch prices and included items after items are imported
            Log::info("=== CALLING createBranchPrices() ===");
            $import->createBranchPrices();
            Log::info("=== createBranchPrices() COMPLETED ===");
            
            Log::info("=== CALLING createIncludedItems() ===");
            $import->createIncludedItems();
            Log::info("=== createIncludedItems() COMPLETED ===");

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import completed. Successfully imported {$successCount} packages/bulk items.";
            
            if ($errorCount > 0) {
                $message .= " {$errorCount} records had errors.";
            }

            if (!empty($errors)) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('import_errors', $errors);
            }

            return redirect()->route('items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error("=== PACKAGE/BULK IMPORT FAILED ===");
            Log::error("Error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
} 