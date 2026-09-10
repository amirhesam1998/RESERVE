<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pest\Concerns\Retrievable;

class Attribute extends Model
{
    protected $guarded = ['id'];
    protected $table = 'attributes';

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    /*     public function units()
    {
        return $this->belongsTo(Unit::class);
    } */
}
