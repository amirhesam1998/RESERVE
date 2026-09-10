<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'];

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'unit_id');
    }
}
