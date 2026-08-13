<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    protected $guarded = [
        'id'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function salons()
    {
        return $this->belongsToMany(Salon::class, 'salon_category');
    }

    public function decentialids()
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->decentialids());
        }

        return $ids;
    }

    public function getFlatTree($prefix = '', &$result = [])
    {
        $result[] = [
            'id' => $this->id,
            'name' => $prefix . $this->name
        ];

        foreach ($this->childrenRecursive as $child) {
            $child->getFlatTree($prefix . '- ', $result);
        }

        return $result;
    }
}
