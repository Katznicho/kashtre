<?php

namespace App\Http\Controllers;

use App\Models\SubGroup;
use Illuminate\Http\Request;

class SubGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \Log::info('SubGroupController::index() called');
        try {
            \Log::info('About to return sub-groups.index view');
            return view('sub-groups.index');
        } catch (\Exception $e) {
            \Log::error('Error in SubGroupController::index(): ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SubGroup $subGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubGroup $subGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubGroup $subGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubGroup $subGroup)
    {
        //
    }
}
