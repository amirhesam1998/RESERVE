<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('/categories/{category}/children', [CategoryController::class, 'children'])->name('categories.children');
    Route::get('/categories/tree', [CategoryController::class, 'tree'])->name('category.tree');
});
