<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    protected string $verifyToken;

    public function __construct()
    {
        $this->verifyToken = config('services.whatsapp.verify_token');
    }

    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        Log::info('WhatsApp Webhook payload:', $request->all());

        // TODO: proses pesan masuk di sini

        return response()->json(['status' => 'ok'], 200);
    }
}