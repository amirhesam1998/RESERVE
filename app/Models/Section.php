<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
