<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'third_party_business_id',
        'third_party_user_id',
        'third_party_username',
        'connection_type',
        'status',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
