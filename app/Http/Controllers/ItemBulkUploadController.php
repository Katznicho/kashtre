<?php

namespace App\Http\Controllers;

use App\Exports\ItemTemplateExport;
use App\Exports\ItemReferenceExport;
use App\Imports\ItemTemplateImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Business;
use App\Models\Group;
use App\Models\Department;
use App\Models\ItemUnit;
use App\Models\ServicePoint;
use App\Models\ContractorProfile;

class ItemBulkUploadController extends Controller
{
    /**
     * Show the bulk upload form
     */
    public function index()
    {
        // Get businesses based on user permissions
        if (Auth::user()->business_id == 1) {
            $businesses = Business::where('id', '!=', 1)->get();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
        }

        return view('items.bulk-upload', compact('businesses'));
    }

    /**
     * Download the template
     */
    public function downloadTemplate(Request $request)
    {
        try {
            // No business_id required for download - template is generic
            $filename = 'items_template.xlsx';
            
            // Log for debugging
            Log::info('Downloading generic items template');
            
            return Excel::download(new ItemTemplateExport(), $filename);
            
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading template: ' . $e->getMessage());
        }
    }

    /**
     * Download the reference sheet for a specific business
     */
    public function downloadReferenceSheet(Request $request)
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
            $filename = 'items_reference_' . str_replace(' ', '_', $business->name) . '.xlsx';

            Log::info('Downloading reference sheet for business: ' . $business->name);

            return Excel::download(new ItemReferenceExport($request->business_id), $filename);

        } catch (\Exception $e) {
            Log::error('Error downloading reference sheet: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading reference sheet: ' . $e->getMessage());
        }
    }

    /**
     * Import the uploaded file
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
            $import = new ItemTemplateImport($request->business_id);
            
            Excel::import($import, $request->file('file'));

            // Create branch prices after items are imported
            $import->createBranchPrices();

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import completed. Successfully imported {$successCount} items.";
            
            if ($errorCount > 0) {
                $message .= " {$errorCount} records had errors.";
            }

            if (!empty($errors)) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('errors', $errors);
            }

            return redirect()->route('items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get filtered data for a specific business (AJAX)
     */
    public function getFilteredData(Request $request)
    {
        $businessId = $request->input('business_id');
        
        if (!$businessId) {
            return response()->json([]);
        }

        // Check permissions
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $businessId) {
            return response()->json([]);
        }

        $data = [
            'groups' => Group::where('business_id', $businessId)->get(),
            'departments' => Department::where('business_id', $businessId)->get(),
            'itemUnits' => ItemUnit::where('business_id', $businessId)->get(),
            'servicePoints' => ServicePoint::where('business_id', $businessId)->get(),
            'contractors' => ContractorProfile::with('business')->where('business_id', $businessId)->get(),
        ];

        return response()->json($data);
    }

    /**
     * Test download method for debugging
     */
    public function testDownload()
    {
        try {
            // Create a simple array export for testing
            $data = [
                ['Name', 'Code', 'Type'],
                ['Test Item 1', 'ITEM001', 'service'],
                ['Test Item 2', 'ITEM002', 'good'],
            ];
            
            return Excel::download(new \Maatwebsite\Excel\Concerns\FromArray($data), 'test_template.xlsx');
            
        } catch (\Exception $e) {
            Log::error('Test download error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 