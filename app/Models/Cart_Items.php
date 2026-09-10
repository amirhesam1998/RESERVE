<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart_Items extends Model
{
    protected $guarded = ['id'];
    protected $table = 'carts_items';

    public function carts()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function price(){
        return $this->belongsTo(price::class);
    }

    public function showTime(){
        return $this->belongsTo(Showtime::class, 'showtime_id');
    }
}
