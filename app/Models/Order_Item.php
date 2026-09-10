<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_Item extends Model
{
    protected $guarded = ['id'];
    protected $table = 'orders_items';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /*  public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function prices()
    {
        return $this->belongsToMany(Price::class);
    } */
}
