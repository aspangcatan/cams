<?php

use App\Http\Controllers\Auth\SessionAuthController;
use App\Http\Middleware\EnsureSessionAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [SessionAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [SessionAuthController::class, 'login'])->name('login.attempt');

Route::middleware(EnsureSessionAuthenticated::class)->group(function () {
    Route::get('/', [SessionAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [SessionAuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [SessionAuthController::class, 'changePassword'])->name('password.change');
});
