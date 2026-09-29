<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Google\Client as GoogleClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:user,email',
            'password' => 'required|string|min:8|confirmed',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'aktif';

        // User baru selalu free_user.
        $data['role'] = 'free_user';

        $user = User::create($data);

        $token = $user
            ->createToken('mobile')
            ->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $data['email'])->first();

    // Debug 1: Cek apakah user ditemukan
    if (!$user) {
        return response()->json(['message' => 'User dengan email tersebut tidak ditemukan!'], 404);
    }

    // Debug 2: Cek password
    if (!Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Password salah / Hash tidak cocok!'], 401);
    }

    // Debug 3: Cek status akun
    if ($user->status !== 'aktif') {
        return response()->json(['message' => "Status akun adalah '{$user->status}', bukan 'aktif'!"], 403);
    }

    // Jika semua lolos:
    $user->tokens()->delete();

    $token = $user->createToken('mobile')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil.',
        'user' => $user,
        'token' => $token,
    ]);
}
public function googleLogin(Request $request)
{
    Log::info('========== GOOGLE LOGIN START ==========');

    try {
        // 1. Validasi request
        $data = $request->validate([
            'id_token' => 'required|string',
        ]);

        Log::info('Google Login: ID Token diterima', [
            'token_exists' => !empty($data['id_token']),
            'token_length' => strlen($data['id_token']),
        ]);

        // 2. Cek konfigurasi Client ID
        $googleClientId = env('GOOGLE_CLIENT_ID');

        Log::info('Google Login: konfigurasi', [
            'google_client_id' => $googleClientId,
            'client_id_exists' => !empty($googleClientId),
        ]);

        if (empty($googleClientId)) {
            Log::error('Google Login ERROR: GOOGLE_CLIENT_ID tidak ditemukan di .env');

            return response()->json([
                'message' => 'Konfigurasi GOOGLE_CLIENT_ID belum tersedia di Laravel.'
            ], 500);
        }

        // 3. Buat Google Client
        $client = new \Google\Client();

        $client->setClientId($googleClientId);

        Log::info('Google Login: Google Client berhasil dibuat');

        // 4. Verifikasi ID Token
        Log::info('Google Login: mulai verifyIdToken()');

        $payload = $client->verifyIdToken($data['id_token']);

        // 5. Token gagal diverifikasi
        if ($payload === false) {

            Log::error('Google Login ERROR: verifyIdToken() mengembalikan FALSE');

            /*
             * Decode payload JWT HANYA untuk mengetahui informasi debugging.
             * Token mentah tidak ditulis ke log.
             */
            $parts = explode('.', $data['id_token']);

            $debugPayload = [];

            if (count($parts) === 3) {
                $decoded = base64_decode(
                    strtr($parts[1], '-_', '+/')
                );

                $debugPayload = json_decode($decoded, true) ?? [];
            }

            Log::error('Google Login: informasi token', [
                'aud' => $debugPayload['aud'] ?? null,
                'iss' => $debugPayload['iss'] ?? null,
                'email' => $debugPayload['email'] ?? null,
                'email_verified' => $debugPayload['email_verified'] ?? null,
                'sub_exists' => isset($debugPayload['sub']),
                'token_exp' => $debugPayload['exp'] ?? null,
                'token_iat' => $debugPayload['iat'] ?? null,
                'expected_client_id' => $googleClientId,
            ]);

            return response()->json([
                'message' => 'Google ID Token tidak valid.'
            ], 401);
        }

        Log::info('Google Login: ID Token berhasil diverifikasi');

        // 6. Tampilkan claim penting ke log
        Log::info('Google Login: payload Google', [
            'sub' => $payload['sub'] ?? null,
            'email' => $payload['email'] ?? null,
            'email_verified' => $payload['email_verified'] ?? null,
            'name' => $payload['name'] ?? null,
            'aud' => $payload['aud'] ?? null,
            'iss' => $payload['iss'] ?? null,
        ]);

        // 7. Cek audience
        if (($payload['aud'] ?? null) !== $googleClientId) {

            Log::error('Google Login ERROR: AUDIENCE TIDAK COCOK', [
                'token_aud' => $payload['aud'] ?? null,
                'expected_aud' => $googleClientId,
            ]);

            return response()->json([
                'message' => 'Google ID Token memiliki audience yang salah.'
            ], 401);
        }

        // 8. Cek issuer
        $issuer = $payload['iss'] ?? null;

        if (!in_array($issuer, [
            'accounts.google.com',
            'https://accounts.google.com',
        ], true)) {

            Log::error('Google Login ERROR: ISSUER TIDAK VALID', [
                'token_issuer' => $issuer,
            ]);

            return response()->json([
                'message' => 'Issuer Google ID Token tidak valid.'
            ], 401);
        }

        // 9. Ambil data Google
        $googleId = $payload['sub'] ?? null;
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'Pengguna Google';

        Log::info('Google Login: data akun', [
            'google_id_exists' => !empty($googleId),
            'email' => $email,
            'name' => $name,
        ]);

        if (!$googleId || !$email) {

            Log::error('Google Login ERROR: data akun Google tidak lengkap', [
                'google_id_exists' => !empty($googleId),
                'email_exists' => !empty($email),
            ]);

            return response()->json([
                'message' => 'Data akun Google tidak lengkap.'
            ], 422);
        }

        // 10. Cek email verified
        if (($payload['email_verified'] ?? false) !== true) {

            Log::error('Google Login ERROR: email belum terverifikasi', [
                'email' => $email,
            ]);

            return response()->json([
                'message' => 'Email Google belum terverifikasi.'
            ], 403);
        }

        // 11. Cari user
        $user = User::where('email', $email)->first();

        if ($user) {

            Log::info('Google Login: user ditemukan', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ]);

        } else {

            Log::info('Google Login: user belum ada, membuat user baru', [
                'email' => $email,
            ]);

            $user = User::create([
                'nama' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'status' => 'aktif',
                'role' => 'free_user',
            ]);

            Log::info('Google Login: user baru berhasil dibuat', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        // 12. Cek status
        if ($user->status !== 'aktif') {

            Log::error('Google Login ERROR: akun tidak aktif', [
                'user_id' => $user->id,
                'status' => $user->status,
            ]);

            return response()->json([
                'message' => "Status akun adalah '{$user->status}', bukan 'aktif'."
            ], 403);
        }

        // 13. Hapus token lama
        Log::info('Google Login: menghapus token Sanctum lama', [
            'user_id' => $user->id,
        ]);

        $user->tokens()->delete();

        // 14. Buat token baru
        $token = $user
            ->createToken('mobile')
            ->plainTextToken;

        Log::info('Google Login: token Sanctum berhasil dibuat', [
            'user_id' => $user->id,
        ]);

        Log::info('========== GOOGLE LOGIN SUCCESS ==========');

        return response()->json([
            'message' => 'Login Google berhasil.',
            'user' => $user,
            'token' => $token,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {

        Log::error('Google Login ERROR: VALIDATION', [
            'errors' => $e->errors(),
        ]);

        throw $e;

    } catch (\Throwable $e) {

        Log::error('========== GOOGLE LOGIN EXCEPTION ==========', [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Gagal memproses Google Login.'
        ], 500);
    }
}


    public function logout(Request $request)
    {
        $request
            ->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }


    public function me(Request $request)
    {
        return response()->json(
            $request->user()
        );
    }


    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nama' => 'sometimes|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|string|max:255',
        ]);

        $user->update($data);

        return response()->json(
            $user->fresh()
        );
    }

    public function showLogin()
{
    return view('auth.login');
}

public function webLogin(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!auth()->attempt($credentials)) {
        return back()
            ->withErrors([
                'email' => 'Email atau password salah.'
            ])
            ->withInput($request->only('email'));
    }

    $request->session()->regenerate();

    $user = auth()->user();

    if (
        !in_array($user->role, [
            'admin',
            'owner',
            'developer'
        ])
    ) {
        auth()->logout();

        return back()->withErrors([
            'email' => 'Akun tidak memiliki akses ke panel admin.'
        ]);
    }

    return redirect()->intended('/admin');
}

public function webLogout(Request $request)
{
    auth()->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with('success', 'Berhasil logout.');
}
}