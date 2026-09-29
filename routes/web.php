<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/admin');
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        AuthController::class,
        'showLogin'
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'webLogin'
    ])->name('login.process');

});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->group(function () {

        Route::get('/', [
            DashboardController::class,
            'index'
        ])->middleware('role:admin,owner,developer');

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ])->middleware('role:admin,owner,developer');

        Route::post('/logout', [
            AuthController::class,
            'webLogout'
        ])->name('logout');
    });