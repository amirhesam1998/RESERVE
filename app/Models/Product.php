<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];
    protected $table = 'products';

    public function prices()
    {
        return $this->hasMany(Price::class, 'product_id');
    }

    public function seat()
    {
        return $this->hasOne(Seat::class, 'product_id');
    }

    public function reserve()
    {
        return $this->hasOne(Reserve::class);
    }

    public function cartItem()
    {
        return $this->hasMany(Cart_Items::class);
    }

    public function images(){
        return $this->morphMany(Image::class, 'imageable');
    }
}
