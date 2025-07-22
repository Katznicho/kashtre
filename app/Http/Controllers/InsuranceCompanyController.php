<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use Illuminate\Http\Request;

class InsuranceCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('insurance-company.index');
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
    public function show(InsuranceCompany $insuranceCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InsuranceCompany $insuranceCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsuranceCompany $insuranceCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsuranceCompany $insuranceCompany)
    {
        //
    }
}
