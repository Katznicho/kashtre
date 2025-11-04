<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CreditNoteWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'business_id',
        'default_supervisor_user_id',
        'finance_user_id',
        'ceo_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function defaultSupervisor()
    {
        return $this->belongsTo(User::class, 'default_supervisor_user_id');
    }

    public function finance()
    {
        return $this->belongsTo(User::class, 'finance_user_id');
    }

    public function ceo()
    {
        return $this->belongsTo(User::class, 'ceo_user_id');
    }

    protected static function booted()
    {
        static::creating(function ($workflow) {
            $workflow->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
