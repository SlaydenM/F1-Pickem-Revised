<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PickController;
use App\Http\Controllers\PrivateImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/picks', [PickController::class, 'index'])->name('picks.index');
    Route::post('/picks/submit', [PickController::class, 'submit'])->name('picks.submit');
    Route::get('/picks/view', [PickController::class, 'view'])->name('picks.view');
    Route::get('/logos/{year}/{filename}', [PrivateImageController::class, 'show'])->name('driver.logo');
});


// Route::middleware('auth')->group(function () {
// });