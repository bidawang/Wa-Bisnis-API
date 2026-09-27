<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/webhook', [WhatsappWebhookController::class, 'verify']);
Route::post('/webhook', [WhatsappWebhookController::class, 'handle']);
