<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'business_id',
        'branch_id',
        'client_id',
        'visit_id',
        'name',
        'nin',
        'surname',
        'first_name',
        'other_names',
        'id_passport_no',
        'sex',
        'date_of_birth',
        'marital_status',
        'occupation',
        'phone_number',
        'address',
        'email',
        'services_category',
        'preferred_payment_method',
        'payment_phone_number',
        // Next of Kin details
        'nok_surname',
        'nok_first_name',
        'nok_other_names',
        'nok_marital_status',
        'nok_occupation',
        'nok_phone_number',
        'nok_physical_address',
        'balance',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the full name of the client
     */
    public function getFullNameAttribute()
    {
        return trim($this->surname . ' ' . $this->first_name . ' ' . ($this->other_names ?? ''));
    }

    /**
     * Get the full name of the next of kin
     */
    public function getNokFullNameAttribute()
    {
        return trim($this->nok_surname . ' ' . $this->nok_first_name . ' ' . ($this->nok_other_names ?? ''));
    }

    /**
     * Generate a unique client ID based on NIN, business, and branch
     */
    public static function generateClientId($nin, $business, $branch)
    {
        // Get business prefix (first 2 letters of business name)
        $businessPrefix = strtoupper(substr($business->name, 0, 2));
        
        // Get branch prefix (first letter of branch name)
        $branchPrefix = strtoupper(substr($branch->name, 0, 1));
        
        // Get NIN suffix (last 4 digits of NIN, or random if no NIN)
        if ($nin) {
            $ninSuffix = strtoupper(substr($nin, -4));
        } else {
            $ninSuffix = strtoupper(Str::random(4));
        }
        
        // Format: BusinessPrefix + BranchPrefix + NINSuffix
        return $businessPrefix . $branchPrefix . $ninSuffix;
    }

    /**
     * Generate a visit ID based on business and branch
     */
    public static function generateVisitId($business, $branch)
    {
        // Get the first letter of the first two words of business name
        $businessWords = explode(' ', $business->name);
        $businessPrefix = '';
        if (count($businessWords) >= 2) {
            $businessPrefix = strtoupper(substr($businessWords[0], 0, 1) . substr($businessWords[1], 0, 1));
        } else {
            $businessPrefix = strtoupper(substr($business->name, 0, 2));
        }

        // Get the first letter of branch name
        $branchLetter = strtoupper(substr($branch->name, 0, 1));

        // Get today's count for this business and branch
        $todayCount = self::where('business_id', $business->id)
            ->where('branch_id', $branch->id)
            ->whereDate('created_at', today())
            ->count() + 1;

        // Format the count as 2-digit number
        $countStr = str_pad($todayCount, 2, '0', STR_PAD_LEFT);

        // If count exceeds 99, reset to 01 and increment branch letter
        if ($todayCount > 99) {
            $countStr = '01';
            $branchLetter = chr(ord($branchLetter) + 1);
        }

        return $businessPrefix . $countStr . $branchLetter;
    }

    /**
     * Get the age of the client
     */
    public function getAgeAttribute()
    {
        if ($this->date_of_birth) {
            return now()->diffInYears($this->date_of_birth);
        }
        return null;
    }
}
