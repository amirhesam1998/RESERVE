<?php

use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return redirect()->route('login');
})->name('/');

// -------------------- USER CRUD -------------------------------
Route::middleware('jwt')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admins', [UserController::class, 'admins'])->name('user.admins');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/password/edit', [UserController::class, 'editPassForm'])->name('user.editpass.form');
    Route::patch('/users/{user}/password', [UserController::class, 'editPass'])->name('user.editPass');
    Route::get('/user/newuser', [UserController::class, 'createNewUserForm'])->name('user.newuserform');
    Route::post('/user/newuser', [UserController::class, 'createNewUser'])->name('user.newuser');
    Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');
});

Route::middleware('jwt.guest')->group(function () {
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/login', [UserController::class, 'loginForm'])->name('login');
});
