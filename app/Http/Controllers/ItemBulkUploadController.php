<?php

namespace App\Http\Controllers;

use App\Exports\ItemTemplateExport;
use App\Imports\ItemTemplateImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $businesses = Business::all();
        } else {
            $businesses = Business::where('id', Auth::user()->business_id)->get();
        }

        return view('items.bulk-upload', compact('businesses'));
    }

    /**
     * Download the template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ItemTemplateExport(), 'items_template.xlsx');
    }

    /**
     * Import the uploaded file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $import = new ItemTemplateImport();
            
            Excel::import($import, $request->file('file'));

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
} 