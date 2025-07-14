<?php

namespace App\Http\Controllers;

use App\Models\BusinessDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('business-documents.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
            ]);

            $file = $request->file('document_file');
            $path = $file->store('business_documents', 'public');

            BusinessDocument::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => Auth::id(),
                'business_id' => Auth::user()->business_id,
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $path,
                'status' => 'pending',
            ]);

            return redirect()->back()->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        try {
            $document = BusinessDocument::where('uuid', $uuid)->firstOrFail();

            return view('business-documents.show', compact('document'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Document not found.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        try {
            $document = BusinessDocument::where('uuid', $uuid)->firstOrFail();

            return view('business-documents.edit', compact('document'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Document not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        try {
            $document = BusinessDocument::where('uuid', $uuid)->firstOrFail();

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Optional
            ]);

            if ($request->hasFile('document_file')) {
                // Delete old file
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('document_file');
                $path = $file->store('business_documents', 'public');
                $document->file_path = $path;
            }

            $document->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating document: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        try {
            $document = BusinessDocument::where('uuid', $uuid)->firstOrFail();

            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return redirect()->back()->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }
}
