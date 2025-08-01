<?php

namespace App\Http\Controllers;

use App\Exports\ContractorProfileTemplateExport;
use App\Imports\ContractorProfileTemplateImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Business;
use App\Models\User;

class ContractorProfileBulkUploadController extends Controller
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

        return view('contractor-profiles.bulk-upload', compact('businesses'));
    }

    /**
     * Download the template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ContractorProfileTemplateExport(), 'contractor_profiles_template.xlsx');
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
            $import = new ContractorProfileTemplateImport();
            
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import completed. Successfully imported {$successCount} contractor profiles.";
            
            if ($errorCount > 0) {
                $message .= " {$errorCount} records had errors.";
            }

            if (!empty($errors)) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('errors', $errors);
            }

            return redirect()->route('contractor-profiles.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get users for a specific business (AJAX)
     */
    public function getUsers(Request $request)
    {
        $businessId = $request->input('business_id');
        
        if (!$businessId) {
            return response()->json([]);
        }

        // Check permissions
        if (Auth::user()->business_id != 1 && Auth::user()->business_id != $businessId) {
            return response()->json([]);
        }

        $users = User::where('business_id', $businessId)
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' (' . $user->email . ')'
                ];
            });

        return response()->json($users);
    }
} 