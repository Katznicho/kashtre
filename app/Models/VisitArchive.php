<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitArchive extends Model
{
    use HasFactory;

    protected $table = 'visit_archives';

    protected $fillable = [
        'record_type',
        'business_id',
        'branch_id',
        'client_id',
        'client_name',
        'client_age',
        'visit_id',
        'archived_at',
        'visit_end_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'visit_end_at' => 'datetime',
        'client_age' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

