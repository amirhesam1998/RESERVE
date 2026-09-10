<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    protected $guarded = [
        'id'
    ];
    protected $table= "showtimes";

    public function salons()
    {
        return $this->belongsToMany(Salon::class, 'salons_showtimes');
    }

    protected function casts()
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i'
        ];
    }

    public function reserves(){
        return $this->hasMany(Reserve::class);
    }

    public function cartItems(){
        return $this->hasMany(Cart_Items::class);
    }
}
