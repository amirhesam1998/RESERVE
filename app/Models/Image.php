<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $guarded=['id'];
    protected $table='images';
    protected $appends = ['url'];

    public function imageable(){
        return $this->morphTo();
    }

    public function getUrlAttribute(){
        return Storage::disk($this->disk)->url($this->path);
    }
}
