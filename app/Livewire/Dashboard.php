<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ContractorProfile;
use App\Models\ContractorBalanceHistory;
use App\Models\BusinessBalanceHistory;

class Dashboard extends Component
{
    public $business;
    public $currentBranch;
    public $balance;
    public $lastUpdate;
    public $isContractor = false;
    public $contractorProfile;
    public $assignedServicePoints;

    public function mount()
    {
        $user = Auth::user();

        // Check if user is a contractor
        $this->contractorProfile = ContractorProfile::where('user_id', $user->id)->first();
        $this->isContractor = $this->contractorProfile !== null;

        if ($this->isContractor) {
            // Contractor dashboard - calculate balance from contractor_balance_histories (source of truth)
            $balanceHistory = \App\Models\ContractorBalanceHistory::where('contractor_profile_id', $this->contractorProfile->id)
                ->selectRaw('SUM(CASE WHEN type = "credit" THEN amount ELSE -amount END) as calculated_balance')
                ->first();
            
            $this->balance = $balanceHistory ? $balanceHistory->calculated_balance : 0;
            
            // Get assigned service points for this contractor
            $this->assignedServicePoints = \App\Models\ServicePoint::whereHas('serviceDeliveryQueues', function($query) use ($user) {
                $query->where('started_by_user_id', $user->id);
            })->distinct()->get();
        } else {
            // Regular staff dashboard
            $this->business = $user->business;
            $this->currentBranch = $user->current_branch;
            
            // Get the actual business account balance from MoneyAccount
            // For Kashtre (business_id = 1), show kashtre_account balance
            // For other businesses, show business_account balance
            // Get the actual business account balance from MoneyAccount (source of truth)
            $businessAccount = \App\Models\MoneyAccount::where('business_id', $this->business->id)
                ->where('account_type', 'business_account')
                ->first();
            
            $this->balance = $businessAccount ? $businessAccount->balance : 0;
        }

        $this->lastUpdate = now()->format('H:i:s');
    }

    public function render()
    {
        if ($this->isContractor) {
            return view('livewire.contractor-dashboard');
        }
        
        return view('livewire.dashboard');
    }
}
