<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function store(Request $request, ?Tagihan $tagihan = null)
    {
        if ($tagihan) {
            $this->authorizeTagihan($tagihan);
        }

        $validated = $request->validate([
            'id_tagihan' => ['required_without:id_sewa', 'nullable', 'integer'],
            'id_sewa' => ['required_without:id_tagihan', 'nullable', 'integer'],
            'jumlah' => ['required', 'numeric', 'min:1'],
            'metode' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $tagihan, $request) {

            $tagihan ??= Tagihan::findOrFail(
                $validated['id_tagihan']
            );

            $this->authorizeTagihan($tagihan);

            $jumlah = $validated['jumlah'];

            if ($jumlah > $tagihan->sisa) {
                abort(422, 'Jumlah pembayaran melebihi sisa tagihan.');
            }

            $pembayaran = Pembayaran::create([
                'id_tagihan' => $tagihan->id_tagihan,
                'id_sewa' => $tagihan->id_sewa,
                'id_user' => $request->user()->id_user,
                'id_tempat' => $tagihan->id_tempat,
                'jumlah' => $jumlah,
                'metode' => $validated['metode'],
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'berhasil',
            ]);

            $dibayar = $tagihan->dibayar + $jumlah;
            $sisa = $tagihan->total - $dibayar;

            $tagihan->update([
                'dibayar' => $dibayar,
                'sisa' => max(0, $sisa),
                'status' => $sisa <= 0
                    ? 'lunas'
                    : 'sebagian',
            ]);

            return response()->json([
                'message' => 'Pembayaran berhasil.',
                'data' => [
                    'pembayaran' => $pembayaran,
                    'tagihan' => $tagihan->fresh()
                ]
            ], 201);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pembayaran::query();

        if ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_pembayaran')->paginate(20)
        ]);
    }

    public function update(
        Request $request,
        Pembayaran $pembayaran
    ) {
        $this->authorizePembayaran($pembayaran);

        $validated = $request->validate([
            'status' => ['required', 'in:berhasil,batal'],
            'catatan' => ['nullable', 'string'],
        ]);

        $pembayaran->update($validated);

        return response()->json([
            'message' => 'Pembayaran berhasil diperbarui.',
            'data' => $pembayaran->fresh()
        ]);
    }

    public function destroy(Pembayaran $pembayaran)
    {
        $this->authorizePembayaran($pembayaran);

        $pembayaran->delete();

        return response()->json([
            'message' => 'Pembayaran berhasil dihapus.'
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

    protected function authorizePembayaran(Pembayaran $pembayaran)
    {
        $user = Auth::user();

        if (
            $user->role !== 'developer' &&
            $pembayaran->id_tempat !== $user->id_tempat
        ) {
            abort(403);
        }
    }
}