<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Display the refunds dashboard for the current user.
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $permissions = $user->permissions ?? [];

        $canViewRefunds = in_array('View Finance', $permissions)
            || in_array('View Refunds', $permissions);

        if (! $canViewRefunds) {
            abort(403, 'You do not have permission to view refunds.');
        }

        return view('refunds.index');
    }
}


