<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Salon extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'salon_category')->withPivot('is_main');
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
}
