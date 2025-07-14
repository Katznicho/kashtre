<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessDocument extends Model
{
    use HasFactory;


    protected $fillable = [
        'uuid',
        'user_id',
        'business_id',
        'title',
        'description',
        'file_path',
        'status',
    ];


    /**
     * Get the user that owns the business document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business that owns the document.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the file's URL.
     */
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
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
