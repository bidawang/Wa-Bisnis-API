<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\TempatSewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Paket::with('detail');

        if ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_paket')->paginate(20)
        ]);
    }

    public function publicIndex(TempatSewa $tempatSewa)
    {
        return response()->json([
            'data' => Paket::with('detail')
                ->where('id_tempat', $tempatSewa->id_tempat)
                ->where('status', 'aktif')
                ->latest('id_paket')
                ->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket' => ['required', 'string', 'max:255'],
            'deskripsi'  => ['nullable', 'string'],
            'harga'      => ['required', 'numeric', 'min:0'],
            'status'     => ['required', 'in:aktif,nonaktif'],
            'detail'     => ['nullable', 'array'],
            'detail.*.id_barang' => ['required_with:detail', 'integer'],
            'detail.*.jumlah'    => ['required_with:detail', 'numeric', 'min:1'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($validated, $user) {

            $paket = Paket::create([
                'id_tempat'  => $user->id_tempat,
                'nama_paket' => $validated['nama_paket'],
                'deskripsi'  => $validated['deskripsi'] ?? null,
                'harga'      => $validated['harga'],
                'status'     => $validated['status'],
            ]);

            foreach ($validated['detail'] ?? [] as $detail) {
                $paket->detail()->create([
                    'id_barang' => $detail['id_barang'],
                    'jumlah'    => $detail['jumlah'],
                ]);
            }

            return response()->json([
                'message' => 'Paket berhasil dibuat.',
                'data' => $paket->load('detail')
            ], 201);
        });
    }

    public function show(Paket $paket)
    {
        $this->authorizePaket($paket);

        return response()->json([
            'data' => $paket->load('detail')
        ]);
    }

    public function update(Request $request, Paket $paket)
    {
        $this->authorizePaket($paket);

        $validated = $request->validate([
            'nama_paket' => ['sometimes', 'string', 'max:255'],
            'deskripsi'  => ['sometimes', 'nullable', 'string'],
            'harga'      => ['sometimes', 'numeric', 'min:0'],
            'status'     => ['sometimes', 'in:aktif,nonaktif'],
            'detail'     => ['sometimes', 'array'],
            'detail.*.id_barang' => ['required', 'integer'],
            'detail.*.jumlah'    => ['required', 'numeric', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated, $paket) {

            $paket->update([
                'nama_paket' => $validated['nama_paket'] ?? $paket->nama_paket,
                'deskripsi'  => $validated['deskripsi'] ?? $paket->deskripsi,
                'harga'      => $validated['harga'] ?? $paket->harga,
                'status'     => $validated['status'] ?? $paket->status,
            ]);

            if (array_key_exists('detail', $validated)) {
                $paket->detail()->delete();

                foreach ($validated['detail'] as $detail) {
                    $paket->detail()->create($detail);
                }
            }

            return response()->json([
                'message' => 'Paket berhasil diperbarui.',
                'data' => $paket->fresh('detail')
            ]);
        });
    }

    public function destroy(Paket $paket)
    {
        $this->authorizePaket($paket);

        $paket->delete();

        return response()->json([
            'message' => 'Paket berhasil dihapus.'
        ]);
    }

    protected function authorizePaket(Paket $paket)
    {
        $user = Auth::user();

        if (
            $user->role !== 'developer' &&
            $user->id_tempat !== $paket->id_tempat
        ) {
            abort(403, 'Akses paket ditolak.');
        }
    }
}