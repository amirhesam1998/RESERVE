<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $guarded = [
        'id'
    ];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }
}
