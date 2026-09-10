<?php

use App\Http\Controllers\Web\ImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function (){
    Route::post('/{type}/{id}/images', [ImageController::class, 'store'])->name('images.store');
    Route::delete('/images/{imageId}', [ImageController::class, 'delete']);
    Route::patch('/images/{imageId}/primary', [ImageController::class, 'setPrimary']);
});