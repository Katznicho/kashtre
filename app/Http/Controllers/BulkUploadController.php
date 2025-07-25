<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Business;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DynamicTemplateExport;
use App\Imports\DynamicTemplateImport;

class BulkUploadController extends Controller
{
    //
    // Show form to select business, branch, validation, and upload file
    public function showUploadForm()
    {
        // You might fetch businesses, branches, validations here
        $businesses = Business::where('id', "!=", 1)->get();
        $branches = Branch::all();
        return view('bulk-uploads.validations', compact('businesses', 'branches'));
    }

    // Generate and return Excel template
    public function downloadTemplate(Request $request)
    {
        // We'll create a downloadable Excel with sheets for all modules
        // (to be implemented in next step using Maatwebsite\Excel)

        // For now just a placeholder
        return response()->json(['message' => 'Template download coming soon.']);
    }

    // Handle uploaded Excel file
    public function handleUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
            'validation_type' => 'required',
            'upload_file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // We'll parse and insert later
        return response()->json(['message' => 'Upload handling coming soon.']);
    }



    
    public function generateTemplate(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
        ]);
    
        try {
            $business = Business::findOrFail($validated['business_id']);
            $branch = Branch::findOrFail($validated['branch_id']);
    
            $filename = 'upload_template_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    
            return Excel::download(
                new DynamicTemplateExport($business->name, $branch->name, $validated['items']),
                $filename
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while generating the template: ' . $e->getMessage());
        }
    }


    public function importTemplate(Request $request)
{
    $validated = $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'branch_id' => 'required|exists:branches,id',
        'template' => 'required|file|mimes:xlsx',
    ]);

    try {
        Excel::import(new DynamicTemplateImport($validated['business_id'], $validated['branch_id']), $request->file('template'));

        return redirect()->back()->with('success', 'Template uploaded and processed successfully!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
    }
}
    
}
