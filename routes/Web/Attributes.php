<?php

use App\Http\Controllers\Web\AttributeController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes');
    Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
    Route::post('/attributes/store', [AttributeController::class, 'store'])->name('attributes.store');
    Route::get('/attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
    Route::put('/attributes/{attribute}/update', [AttributeController::class, 'update'])->name('attributes.update');
    Route::delete('/attributes/{attribute}/delete', [AttributeController::class, 'destroy'])->name('attributes.delete');
});
