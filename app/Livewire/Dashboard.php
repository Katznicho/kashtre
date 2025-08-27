<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ContractorProfile;

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
            // Contractor dashboard
            $this->balance = $this->contractorProfile->account_balance;
            
            // Get assigned service points for this contractor
            $this->assignedServicePoints = \App\Models\ServicePoint::whereHas('serviceDeliveryQueues', function($query) use ($user) {
                $query->where('started_by_user_id', $user->id);
            })->distinct()->get();
        } else {
            // Regular staff dashboard
            $this->business = $user->business;
            $this->currentBranch = $user->current_branch;
            $this->balance = $this->business->account_balance;
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
