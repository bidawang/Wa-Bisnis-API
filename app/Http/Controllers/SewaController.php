<?php

namespace App\Http\Controllers;

use App\Models\Sewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SewaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Sewa::with([
            'user',
            'detail',
            'tagihan'
        ]);

        if ($user->role === 'free_user') {
            $query->where('id_user', $user->id_user);
        } elseif ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_sewa')->paginate(20)
        ]);
    }

    public function show(Request $request, Sewa $sewa)
    {
        $this->authorizeSewa($sewa);

        return response()->json([
            'data' => $sewa->load([
                'user',
                'detail',
                'tagihan',
                'pembayaran'
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => ['required', 'integer'],
            'id_tempat' => ['required', 'integer'],
            'id_booking' => ['nullable', 'integer'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'biaya_tambahan' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sewa = Sewa::create([
            ...$validated,
            'biaya_tambahan' => $validated['biaya_tambahan'] ?? 0,
            'dibayar' => 0,
            'sisa' => $validated['total'],
            'status' => 'aktif',
        ]);

        return response()->json([
            'message' => 'Sewa berhasil dibuat.',
            'data' => $sewa
        ], 201);
    }

    public function update(Request $request, Sewa $sewa)
    {
        $this->authorizeSewa($sewa);

        $validated = $request->validate([
            'tanggal_mulai' => ['sometimes', 'date'],
            'tanggal_selesai' => ['sometimes', 'date'],
            'biaya_tambahan' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'status' => [
                'sometimes',
                'in:aktif,selesai,dibatalkan'
            ],
            'catatan' => ['sometimes', 'nullable', 'string'],
        ]);

        $sewa->update($validated);

        return response()->json([
            'message' => 'Sewa berhasil diperbarui.',
            'data' => $sewa->fresh()
        ]);
    }

    public function destroy(Sewa $sewa)
    {
        $this->authorizeSewa($sewa);

        if ($sewa->status === 'selesai') {
            return response()->json([
                'message' => 'Sewa yang sudah selesai tidak dapat dihapus.'
            ], 422);
        }

        $sewa->delete();

        return response()->json([
            'message' => 'Sewa berhasil dihapus.'
        ]);
    }

    protected function authorizeSewa(Sewa $sewa)
    {
        $user = Auth::user();

        if ($user->role === 'free_user') {
            if ($sewa->id_user !== $user->id_user) {
                abort(403);
            }

            return;
        }

        if (
            $user->role !== 'developer' &&
            $sewa->id_tempat !== $user->id_tempat
        ) {
            abort(403);
        }
    }
}