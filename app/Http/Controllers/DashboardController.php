<?php

namespace App\Http\Controllers;

use App\Models\DataFeed;
use App\Models\FundRaiser;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {

      
        $user = Auth::user();
        $business = $user->business; // Make sure 'business' relationship exists
        $branch = $user->branch;
    

        return view('pages/dashboard/dashboard', compact('business', 'branch'));
    }

    /**
     * Displays the analytics screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function analytics()
    {
        return view('pages/dashboard/analytics');
    }

    /**
     * Displays the fintech screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function fintech()
    {
        return view('pages/dashboard/fintech');
    }
}
