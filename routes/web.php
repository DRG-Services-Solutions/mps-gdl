<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    
    Route::resource('products', ProductController::class);
    Route::resource('manufacturers', ManufacturerController::class);
    Route::resource('categories', CategoryController::class);


    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware(['auth', 'verified']) 
        ->name('users.toggle-status');
});

require __DIR__.'/auth.php';
