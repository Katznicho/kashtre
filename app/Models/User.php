<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Support\Str;




class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'business_id',
        'branch_id', // Uncomment if you want to allow branch assignment,
        'service_points',
        'permissions',
        'allowed_branches',
        'qualification_id',
        'department_id',
        'section_id',
        'title_id',
        'gender',
        'phone',
        'nin',
        'profile_photo_path',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'service_points' => 'array',
        'permissions' => 'array',
        'allowed_branches' => 'array',
        'gender' => 'string',
        'phone' => 'string',
        'nin' => 'string',
        'profile_photo_path' => 'string',
        'email_verified_at' => 'datetime',
        'remember_token' => 'string',
        'status' => 'string',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function contractorProfile()
    {
        return $this->hasOne(ContractorProfile::class);
    }

    /**
     * Get the current working branch for the user
     */
    public function getCurrentBranchAttribute()
    {
        $currentBranchId = session('current_branch_id', $this->branch_id);
        
        // If we have a branch ID, try to find the branch
        if ($currentBranchId) {
            $branch = Branch::find($currentBranchId);
            if ($branch) {
                return $branch;
            }
        }
        
        // Fallback to the user's assigned branch
        if ($this->branch_id) {
            $branch = Branch::find($this->branch_id);
            if ($branch) {
                return $branch;
            }
        }
        
        // If no branch is found, return null
        return null;
    }

    /**
     * Get the service points assigned to this user
     */
    public function servicePoints()
    {
        if ($this->service_points) {
            return ServicePoint::whereIn('id', $this->service_points);
        }
        return collect();
    }

    /**
     * Get the service queues for this user's service points
     */
    public function serviceQueues()
    {
        if ($this->service_points) {
            return ServiceQueue::whereIn('service_point_id', $this->service_points);
        }
        return collect();
    }

    /**
     * Get pending queues for user's service points
     */
    public function pendingQueues()
    {
        return $this->serviceQueues()->pending()->orderBy('queue_number');
    }

    /**
     * Get in-progress queues for user's service points
     */
    public function inProgressQueues()
    {
        return $this->serviceQueues()->inProgress()->orderBy('started_at');
    }

    /**
     * Get completed queues for user's service points today
     */
    public function completedQueuesToday()
    {
        return $this->serviceQueues()->completed()->today()->orderBy('completed_at', 'desc');
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
