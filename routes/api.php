<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*
 * Default Laravel Sanctum user endpoint.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| WhatsApp Webhook
|--------------------------------------------------------------------------
|
| GET  → Verifikasi webhook oleh Meta
| POST → Menerima pesan WhatsApp
|
*/

Route::get('/webhook', [
    WhatsappWebhookController::class,
    'verify'
]);

Route::post('/webhook', [
    WhatsappWebhookController::class,
    'handle'
]);
