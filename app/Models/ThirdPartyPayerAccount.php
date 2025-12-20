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
    ];

    /**
     * Mutator to hash password when setting
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

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
     * For session-based authentication, this should return the primary key name ('id')
     * to allow the provider to retrieve users by ID from the session.
     * We override retrieveByCredentials in the provider to use 'username' for login.
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName(); // Returns 'id' for session-based retrieval
    }

    /**
     * Get the unique identifier for the user.
     * This returns the integer ID for session storage.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey(); // Returns the integer 'id' for session storage
    }
}
