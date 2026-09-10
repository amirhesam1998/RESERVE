<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $guarded = ['id'];
    protected $table = 'prices';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute_values()
    {
        return $this->belongsToMany(AttributeValue::class, 'price_attribute_value');
    }

    public function cartItems(){
        return $this->hasMany(Cart_Items::class);
    }
}
