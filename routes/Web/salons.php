<?php


use App\Http\Controllers\Web\SalonController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('/salons', [SalonController::class, 'index'])->name('salons.index');
    Route::get('/salons/create', [SalonController::class, 'create'])->name('salons.create');
    Route::get('/salons/{salon}/edit', [SalonController::class, 'edit'])->name('salons.edit');
    Route::put('salons/{salon}', [SalonController::class, 'update'])->name('salons.update');
    Route::delete('/salons/{salon}', [SalonController::class, 'destroy'])->name('salons.destroy');
    Route::get('/salons/{salon}/layout', [SalonController::class, 'layout'])->name('salons.layout');
});
