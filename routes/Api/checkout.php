<?php

use App\Http\Controllers\Api\Cart;
use App\Http\Controllers\Api\CheckOut;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/checkout', [CheckOut::class, 'checkOut'])->name('checkout');
});
