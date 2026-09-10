<?php

use App\Http\Controllers\Web\SessionController;
use App\Http\Controllers\Web\ShowtimeController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('/sessions', [ShowtimeController::class, 'index'])->name('sessions.index');
    Route::get('/session/create', [ShowtimeController::class, 'create'])->name('session.create');
    Route::post('/session/store', [ShowtimeController::class, 'store'])->name('session.store');
    Route::get('sessions/{showtime}/edit', [ShowtimeController::class, 'edit'])->name('sessions.edit');
    Route::put('sessions/{showtime}/update', [ShowtimeController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/{showtime}/delete', [ShowtimeController::class, 'destroy'])->name('sessions.delete');
});
