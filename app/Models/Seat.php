<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $guarded = [
        'id'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
