<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TempatSewaController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SewaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\DompetController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WhatsappWebhookController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [
    AuthController::class,
    'register'
]);

Route::post('/auth/login', [
    AuthController::class,
    'login'
]);

Route::post('/auth/google', [
    AuthController::class,
    'googleLogin'
]);

/*
|--------------------------------------------------------------------------
| WHATSAPP WEBHOOK
|--------------------------------------------------------------------------
*/

Route::get('/webhook/whatsapp', [
    WhatsappWebhookController::class,
    'verify'
]);

Route::post('/webhook/whatsapp', [
    WhatsappWebhookController::class,
    'handle'
]);

/*
|--------------------------------------------------------------------------
| PUBLIC API
|--------------------------------------------------------------------------
|
| Tidak membutuhkan login.
|
*/

Route::prefix('public')->group(function () {

    Route::get('/tempat-sewa', [
        TempatSewaController::class,
        'publicIndex'
    ]);

    Route::get('/tempat-sewa/{tempatSewa}', [
        TempatSewaController::class,
        'publicShow'
    ]);

    Route::get('/tempat-sewa/{tempatSewa}/barang', [
        BarangController::class,
        'publicIndex'
    ]);

    Route::get('/tempat-sewa/{tempatSewa}/paket', [
        PaketController::class,
        'publicIndex'
    ]);

});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH / PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/profile', [
        AuthController::class,
        'me'
    ]);

    Route::put('/profile', [
        AuthController::class,
        'updateProfile'
    ]);

    Route::post('/auth/logout', [
        AuthController::class,
        'logout'
    ]);


    /*
    |--------------------------------------------------------------------------
    | FREE USER
    |--------------------------------------------------------------------------
    */

    Route::apiResource('booking', BookingController::class)
        ->only([
            'index',
            'store',
            'show',
            'update',
            'destroy'
        ]);

    Route::post('/booking/{booking}/cancel', [
        BookingController::class,
        'cancel'
    ]);

    Route::get('/sewa', [
        SewaController::class,
        'index'
    ]);

    Route::get('/sewa/{sewa}', [
        SewaController::class,
        'show'
    ]);

    Route::get('/tagihan', [
        TagihanController::class,
        'index'
    ]);

    Route::get('/tagihan/{tagihan}', [
        TagihanController::class,
        'show'
    ]);

    Route::post('/tagihan/{tagihan}/pembayaran', [
        PembayaranController::class,
        'store'
    ]);


    /*
    |--------------------------------------------------------------------------
    | ADMIN + OWNER + DEVELOPER
    |--------------------------------------------------------------------------
    |
    | Operasional booking/sewa.
    |
    */

    Route::middleware('role:admin,owner,developer')->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ]);

        Route::apiResource('sewa', SewaController::class)
            ->except([
                'index',
                'show'
            ]);

        Route::post('/booking/{booking}/convert-sewa', [
            BookingController::class,
            'convertToSewa'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | OWNER + DEVELOPER
    |--------------------------------------------------------------------------
    |
    | Seluruh bisnis kecuali user.
    |
    */

    Route::middleware('role:owner,developer,admin')->group(function () {

        Route::apiResource('tempat-sewa', TempatSewaController::class)
            ->except([
                'index',
                'show'
            ]);

        Route::apiResource('barang', BarangController::class);

        Route::apiResource('paket', PaketController::class)
            ->except([
                'index',
                'show'
            ]);

        Route::apiResource('tagihan', TagihanController::class)
            ->except([
                'index',
                'show'
            ]);

        Route::apiResource('pembayaran', PembayaranController::class)
            ->except([
                'index',
                'show'
            ]);


        /*
        |--------------------------------------------------------------------------
        | DOMPET
        |--------------------------------------------------------------------------
        */

        Route::get('/dompet', [
            DompetController::class,
            'index'
        ]);

        Route::get('/dompet/{dompet}', [
            DompetController::class,
            'show'
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI DOMPET
        |--------------------------------------------------------------------------
        */

        Route::get('/dompet/{dompet}/transaksi', [
            TransaksiController::class,
            'index'
        ]);

        Route::post('/dompet/{dompet}/transaksi', [
            TransaksiController::class,
            'store'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | DEVELOPER ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:developer')->group(function () {

        Route::apiResource('users', UserController::class);

        Route::get('/developer/dashboard', [
            DashboardController::class,
            'developer'
        ]);
    });
});