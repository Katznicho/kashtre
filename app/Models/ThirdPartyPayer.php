<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ThirdPartyPayer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'business_id',
        'type',
        'insurance_company_id',
        'client_id',
        'name',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'credit_limit',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($payer) {
            $payer->uuid = (string) Str::uuid();
        });
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Helper methods
    public function getPayerNameAttribute()
    {
        if ($this->type === 'insurance_company' && $this->insuranceCompany) {
            return $this->insuranceCompany->name;
        } elseif ($this->type === 'normal_client' && $this->client) {
            return $this->client->name;
        }
        return $this->name;
    }

    public function isInsuranceCompany()
    {
        return $this->type === 'insurance_company';
    }

    public function isNormalClient()
    {
        return $this->type === 'normal_client';
    }

    public function creditLimitChangeRequests()
    {
        return $this->morphMany(CreditLimitChangeRequest::class, 'entity');
    }
}
