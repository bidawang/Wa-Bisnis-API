<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    protected string $verifyToken;
    protected ?string $accessToken;
    protected ?string $phoneNumberId;
    protected string $graphApiVersion;

    public function __construct()
    {
        $this->verifyToken = config('services.whatsapp.verify_token');

        $this->accessToken = config('services.whatsapp.access_token');

        $this->phoneNumberId = config('services.whatsapp.phone_number_id');

        $this->graphApiVersion = config(
            'services.whatsapp.graph_api_version',
            'v23.0'
        );
    }

    /**
     * Verifikasi webhook oleh Meta.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('WhatsApp webhook verification', [
            'mode' => $mode,
        ]);

        if (
            $mode === 'subscribe' &&
            $token === $this->verifyToken
        ) {
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Menerima webhook dari Meta.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp Webhook payload', $payload);

        try {
            /*
             * Pastikan webhook berasal dari event WhatsApp.
             */
            if (
                ($payload['object'] ?? null) !== 'whatsapp_business_account'
            ) {
                return response()->json([
                    'status' => 'ignored',
                ], 200);
            }

            foreach ($payload['entry'] ?? [] as $entry) {

                foreach ($entry['changes'] ?? [] as $change) {

                    if (($change['field'] ?? null) !== 'messages') {
                        continue;
                    }

                    $value = $change['value'] ?? [];

                    /*
                     * Status message seperti sent, delivered,
                     * read, failed, dll tidak diproses sebagai pesan.
                     */
                    if (empty($value['messages'])) {
                        continue;
                    }

                    foreach ($value['messages'] as $message) {
                        $this->processMessage($message, $value);
                    }
                }
            }

            return response()->json([
                'status' => 'ok',
            ], 200);

        } catch (\Throwable $e) {

            Log::error('WhatsApp webhook error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            /*
             * Tetap kembalikan 200 agar Meta tidak terus-menerus
             * menganggap webhook gagal dan melakukan retry.
             */
            return response()->json([
                'status' => 'error',
            ], 200);
        }
    }

    /**
     * Memproses satu pesan WhatsApp.
     */
    protected function processMessage(array $message, array $value): void
    {
        $from = $message['from'] ?? null;
        $type = $message['type'] ?? null;

        if (!$from) {
            return;
        }

        /*
         * Saat ini kita proses pesan teks terlebih dahulu.
         */
        if ($type !== 'text') {

            $this->sendText(
                $from,
                "Saat ini saya baru bisa menerima pesan teks.\n\n"
                . "Ketik *menu* untuk melihat perintah yang tersedia."
            );

            return;
        }

        $text = $message['text']['body'] ?? '';

        $text = trim($text);

        if ($text === '') {
            return;
        }

        Log::info('WhatsApp incoming message', [
            'from' => $from,
            'message_id' => $message['id'] ?? null,
            'text' => $text,
        ]);

        $reply = $this->generateReply($text, $from, $value);

        if ($reply !== null) {
            $this->sendText($from, $reply);
        }
    }

    /**
     * Otak bot sederhana.
     *
     * Nanti bagian ini bisa kita ganti dengan AI.
     */
    protected function generateReply(
        string $text,
        string $from,
        array $value
    ): ?string {

        $command = strtolower(trim($text));

        /*
         * Hapus karakter tambahan agar perintah seperti
         * "MENU!!!" tetap dikenali.
         */
        $command = preg_replace('/[^\p{L}\p{N}\s]/u', '', $command);
        $command = trim($command);

        /*
         * SALAM
         */
        if (in_array($command, [
            'halo',
            'hai',
            'hi',
            'hello',
            'assalamualaikum',
        ])) {
            return
                "Halo! 👋\n\n"
                . "Selamat datang di *Kasir KRJ*.\n\n"
                . "Ketik *menu* untuk melihat layanan yang tersedia.";
        }

        /*
         * MENU
         */
        if (in_array($command, [
            'menu',
            'help',
            'bantuan',
            'start',
        ])) {
            return
                "📋 *MENU KASIR KRJ*\n\n"
                . "1️⃣ *menu* - Tampilkan menu\n"
                . "2️⃣ *status* - Cek status\n"
                . "3️⃣ *produk* - Lihat produk\n"
                . "4️⃣ *bantuan* - Bantuan\n\n"
                . "Ketik salah satu perintah di atas.";
        }

        /*
         * STATUS
         */
        if (
            $command === 'status' ||
            str_starts_with($command, 'status ')
        ) {
            return
                "📦 *CEK STATUS*\n\n"
                . "Fitur pengecekan status pesanan akan "
                . "kita hubungkan ke database kasir pada tahap berikutnya.";
        }

        /*
         * PRODUK
         */
        if (
            $command === 'produk' ||
            $command === 'produk saya'
        ) {
            return
                "🛒 *PRODUK*\n\n"
                . "Fitur daftar produk akan kita hubungkan "
                . "langsung dengan database Laravel.";
        }

        /*
         * BANTUAN
         */
        if ($command === 'bantuan') {
            return
                "ℹ️ *BANTUAN*\n\n"
                . "Ketik *menu* untuk melihat perintah yang tersedia.\n\n"
                . "Contoh:\n"
                . "• halo\n"
                . "• menu\n"
                . "• produk\n"
                . "• status";
        }

        /*
         * PESAN TIDAK DIKENALI
         */
        return
            "Maaf, saya belum memahami pesan tersebut.\n\n"
            . "Ketik *menu* untuk melihat perintah yang tersedia.";
    }

    /**
     * Mengirim pesan teks melalui WhatsApp Cloud API.
     */
    protected function sendText(string $to, string $message): bool
    {
        if (
            empty($this->accessToken) ||
            empty($this->phoneNumberId)
        ) {
            Log::error('WhatsApp configuration belum lengkap.');

            return false;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $this->graphApiVersion,
            $this->phoneNumberId
        );

        try {

            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->failed()) {

                Log::error('WhatsApp send message failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                return false;
            }

            Log::info('WhatsApp message sent', [
                'to' => $to,
                'response' => $response->json(),
            ]);

            return true;

        } catch (\Throwable $e) {

            Log::error('WhatsApp API exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
