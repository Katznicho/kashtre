<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageSales extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'invoice_number',
        'pkn',
        'date',
        'qty',
        'item_name',
        'amount',
        'business_id',
        'branch_id',
        'client_id',
        'package_tracking_id',
        'item_id',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'qty' => 'integer'
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function packageTracking()
    {
        return $this->belongsTo(PackageTracking::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
