<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Tagihan::with([
            'sewa',
            'detail',
            'pembayaran'
        ]);

        if ($user->role === 'free_user') {
            $query->where('id_user', $user->id_user);
        } elseif ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_tagihan')->paginate(20)
        ]);
    }

    public function show(Request $request, Tagihan $tagihan)
    {
        $this->authorizeTagihan($tagihan);

        return response()->json([
            'data' => $tagihan->load([
                'sewa',
                'detail',
                'pembayaran'
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sewa' => ['required', 'integer'],
            'id_user' => ['required', 'integer'],
            'id_tempat' => ['required', 'integer'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'diskon_tipe' => ['nullable', 'in:nominal,persen'],
            'diskon_nilai' => ['nullable', 'numeric', 'min:0'],
            'diskon_nominal' => ['nullable', 'numeric', 'min:0'],
            'biaya_tambahan' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
        ]);

        $tagihan = Tagihan::create([
            ...$validated,
            'diskon_tipe' => $validated['diskon_tipe'] ?? null,
            'diskon_nilai' => $validated['diskon_nilai'] ?? 0,
            'diskon_nominal' => $validated['diskon_nominal'] ?? 0,
            'biaya_tambahan' => $validated['biaya_tambahan'] ?? 0,
            'dibayar' => 0,
            'sisa' => $validated['total'],
            'status' => 'belum_lunas',
        ]);

        return response()->json([
            'message' => 'Tagihan berhasil dibuat.',
            'data' => $tagihan
        ], 201);
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        $this->authorizeTagihan($tagihan);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:belum_lunas,sebagian,lunas,batal'],
            'catatan' => ['sometimes', 'nullable', 'string'],
        ]);

        $tagihan->update($validated);

        return response()->json([
            'message' => 'Tagihan berhasil diperbarui.',
            'data' => $tagihan->fresh()
        ]);
    }

    public function destroy(Tagihan $tagihan)
    {
        $this->authorizeTagihan($tagihan);

        if ($tagihan->dibayar > 0) {
            return response()->json([
                'message' => 'Tagihan yang sudah memiliki pembayaran tidak dapat dihapus.'
            ], 422);
        }

        $tagihan->delete();

        return response()->json([
            'message' => 'Tagihan berhasil dihapus.'
        ]);
    }

    protected function authorizeTagihan(Tagihan $tagihan)
    {
        $user = Auth::user();

        if ($user->role === 'free_user') {
            if ($tagihan->id_user !== $user->id_user) {
                abort(403);
            }

            return;
        }

        if (
            $user->role !== 'developer' &&
            $tagihan->id_tempat !== $user->id_tempat
        ) {
            abort(403);
        }
    }
}