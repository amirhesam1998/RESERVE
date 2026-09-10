<?php

use App\Models\Product;
use Illuminate\Routing\Route;

Route::middleware('jwt')->group(function () {
    Route::post('products/store', [Product::class, 'store'])->name('products.store');
});
