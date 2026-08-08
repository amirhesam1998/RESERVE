<?php

use App\Http\Controllers\Api\SalonController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/salon/create', [SalonController::class, 'store'])->name('api.salons.create');
    Route::get('/salon/{salon}', [SalonController::class, 'show'])->name('salon.show');
    Route::put('/salon/{salon}/layout', [SalonController::class, 'saveLayout'])->name('salon.saveLayout');
});
