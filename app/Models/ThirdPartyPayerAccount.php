<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class ThirdPartyPayerAccount extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'third_party_payer_accounts';

    protected $fillable = [
        'third_party_payer_id',
        'username',
        'password',
        'name',
        'email',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function thirdPartyPayer()
    {
        return $this->belongsTo(ThirdPartyPayer::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Get the name of the unique identifier for the user.
     * This tells Laravel to use 'username' instead of 'email' for authentication lookups
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the unique identifier for the user.
     * This must return the integer ID for session storage, even though we authenticate by username.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey(); // Returns the integer 'id' for session storage
    }
}
