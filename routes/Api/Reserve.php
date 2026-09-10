<?php

use App\Http\Controllers\Api\ReserveController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/reservs/store', [ReserveController::class, 'confirmReservation'])->name('reservs/store');
});
