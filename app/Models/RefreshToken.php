<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
