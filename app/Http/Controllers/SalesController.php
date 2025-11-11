<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display the master sales table for Kashtre (super business) users.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->business_id !== 1) {
            abort(403, 'Access denied.');
        }

        if (!in_array('View Sales', $user->permissions ?? [])) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view sales.');
        }

        return view('sales.index');
    }
}

