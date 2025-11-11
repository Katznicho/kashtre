<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNoteApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'stage',
        'sequence',
        'assigned_user_id',
        'status',
        'acted_by',
        'acted_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acted_at' => 'datetime',
    ];

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function actedByUser()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}


