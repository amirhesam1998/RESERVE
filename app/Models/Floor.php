<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }
}
