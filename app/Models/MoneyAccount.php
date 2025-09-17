<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'business_id',
        'client_id',
        'contractor_profile_id',
        'balance',
        'currency',
        'description',
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contractorProfile()
    {
        return $this->belongsTo(ContractorProfile::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(MoneyTransfer::class, 'from_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(MoneyTransfer::class, 'to_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Methods
    public function debit($amount)
    {
        $this->decrement('balance', $amount);
        $this->save();
        return $this;
    }

    public function credit($amount)
    {
        $this->increment('balance', $amount);
        $this->save();
        return $this;
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    protected static function booted()
    {
        static::creating(function ($account) {
            $account->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
