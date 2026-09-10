<?php

use App\Http\Controllers\Web\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('/carts/show/{user}', [CartController::class, 'showCartItem'])->name('carts.show');
   //Route::post('/carts/items/store', [Cart::class, 'seatAddToCart'])->name('carts.store');
    Route::delete('/carts/items/delete', [Cart::class, 'removeFromCart'])->name('carts.delete');
});