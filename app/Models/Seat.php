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

    public function sessions()
    {
        $this->belongsToMany(Seat::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
