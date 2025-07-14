<?php

namespace App\Http\Controllers;

use App\Models\FloatManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FloatManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return view('float-management.index');
        } catch (\Throwable $e) {
            Log::error('Error loading float index: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to load floats.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('float-management.create');
        } catch (\Throwable $e) {
            Log::error('Error loading float create form: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to load creation form.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'currency' => 'required|in:UGX',
                'channel' => 'required|string|max:255',
                'amount' => 'required|numeric|min:1',
                'date_loaded' => 'required|date',
                'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            // Handle the file upload
            $proofPath = $request->file('proof')->store('float-proofs', 'public');

            // Create the float record
            FloatManagement::create([
                'user_id' => Auth::id(),
                'business_id' => Auth::user()->business_id ?? null,
                'amount' => $request->amount,
                'status' => 'pending',
                'currency' => $request->currency,
                'date' => $request->date_loaded,
                'channel' => $request->channel,
                'proof' => $proofPath,
            ]);

            return redirect()->route('float-management.index')->with('success', 'Float loaded successfully and pending approval.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            // Validation errors
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error storing float: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to save float data.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        try {
            $float = FloatManagement::where('uuid', $uuid)->firstOrFail();
            return view('float-management.show', compact('float'));
        } catch (\Throwable $e) {
            Log::error('Error showing float: ' . $e->getMessage());
            return redirect()->route('float-management.index')->withErrors('Float not found.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        try {
            $float = FloatManagement::where('uuid', $uuid)->firstOrFail();
            return view('float-management.edit', compact('float'));
        } catch (\Throwable $e) {
            Log::error('Error loading float edit form: ' . $e->getMessage());
            return redirect()->route('float-management.index')->withErrors('Float not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        try {
            $float = FloatManagement::where('uuid', $uuid)->firstOrFail();

            $request->validate([
                'currency' => 'required|in:UGX',
                'channel' => 'required|string|max:255',
                'amount' => 'required|numeric|min:1',
                'date_loaded' => 'required|date',
                'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            if ($request->hasFile('proof')) {
                if ($float->proof && Storage::disk('public')->exists($float->proof)) {
                    Storage::disk('public')->delete($float->proof);
                }
                $proofPath = $request->file('proof')->store('float-proofs', 'public');
                $float->proof = $proofPath;
            }

            $float->update([
                'amount' => $request->amount,
                'currency' => $request->currency,
                'date' => $request->date_loaded,
                'channel' => $request->channel,
            ]);

            return redirect()->route('float-management.index')->with('success', 'Float updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error updating float: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to update float data.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        try {
            $float = FloatManagement::where('uuid', $uuid)->firstOrFail();

            if ($float->proof && Storage::disk('public')->exists($float->proof)) {
                Storage::disk('public')->delete($float->proof);
            }

            $float->delete();

            return redirect()->route('float-management.index')->with('success', 'Float record deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Error deleting float: ' . $e->getMessage());
            return redirect()->route('float-management.index')->withErrors('Failed to delete float record.');
        }
    }
}
