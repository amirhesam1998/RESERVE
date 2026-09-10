<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $table = 'attribute_value';
    protected $guarded = ['id'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function prices()
    {
        return $this->belongsToMany(Price::class, 'price_id');
    }
}
