<?php

namespace App\Http\Controllers;

use App\Exports\GoodsServicesTemplateExport;
use App\Imports\GoodsServicesTemplateImport;
use App\Imports\TestImport;
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
     * Show the bulk upload form for goods and services only
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
     * Download the template for goods and services with dropdown data
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
            $filename = 'goods_services_template_' . str_replace(' ', '_', $business->name) . '.xlsx';
            
            // Log for debugging
            Log::info('Downloading goods/services template for business: ' . $business->name);
            
            return Excel::download(new GoodsServicesTemplateExport($request->business_id), $filename);
            
        } catch (\Exception $e) {
            Log::error('Error downloading template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading template: ' . $e->getMessage());
        }
    }

    /**
     * Import the uploaded file for goods and services
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
            $import = new GoodsServicesTemplateImport($request->business_id);
            
            Excel::import($import, $request->file('file'));

            // Create branch prices and service points after items are imported
            $import->createBranchPrices();
            $import->createBranchServicePoints();

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import completed. Successfully imported {$successCount} goods/services.";
            
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
} 