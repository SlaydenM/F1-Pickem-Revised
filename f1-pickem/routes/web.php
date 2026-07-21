<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PickController;
use App\Http\Controllers\PastRacesController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\PrivateImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/next-race', [PickController::class, 'showResult'])->name('next-race');
    Route::get('/next-race/submit', [PickController::class, 'showSubmit'])->name('next-race.submit');
    Route::post('/submit-picks', [PickController::class, 'submit'])->name('submit-picks');
    Route::get('/past-races', [PastRacesController::class, 'index'])->name('past-races');
    Route::get('/rules', [RulesController::class, 'index'])->name('rules');
    // Route::get('/logos/{year}/{filename}', [PrivateImageController::class, 'show'])->name('logos');
});
