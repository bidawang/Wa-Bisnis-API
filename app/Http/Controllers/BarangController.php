<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\TempatSewa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Barang::query();

        if ($user->role !== 'admin') {
            $query->where('id_tempat', $user->id_tempat);
        }

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => $query->latest('id_barang')->paginate(20)
        ]);
    }

    public function publicIndex(TempatSewa $tempatSewa)
    {
        return response()->json([
            'data' => Barang::where('id_tempat', $tempatSewa->id_tempat)
                ->where('status', 'aktif')
                ->latest('id_barang')
                ->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:255'],
            'satuan'    => ['required', 'string', 'max:50'],
            'jenis'     => ['required', 'string', 'max:100'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status'    => ['required', 'in:aktif,nonaktif'],
        ]);

        $validated['id_tempat'] = $request->user()->id_tempat;

        $barang = Barang::create($validated);

        return response()->json([
            'message' => 'Barang berhasil dibuat.',
            'data' => $barang
        ], 201);
    }

    public function show(Barang $barang)
    {
        $this->authorizeBarang($barang);

        return response()->json([
            'data' => $barang->load([
                'stok',
                'foto',
                'tutorial'
            ])
        ]);
    }

    public function update(Request $request, Barang $barang)
    {
        $this->authorizeBarang($barang);

        $validated = $request->validate([
            'nama'      => ['sometimes', 'string', 'max:255'],
            'satuan'    => ['sometimes', 'string', 'max:50'],
            'jenis'     => ['sometimes', 'string', 'max:100'],
            'harga'     => ['sometimes', 'numeric', 'min:0'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'status'    => ['sometimes', 'in:aktif,nonaktif'],
        ]);

        $barang->update($validated);

        return response()->json([
            'message' => 'Barang berhasil diperbarui.',
            'data' => $barang->fresh()
        ]);
    }

    public function destroy(Barang $barang)
    {
        $this->authorizeBarang($barang);

        try {
            $barang->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Barang tidak bisa dihapus karena sudah dipakai di data lain. Ubah statusnya menjadi nonaktif.'
            ], 409);
        }

        return response()->json([
            'message' => 'Barang berhasil dihapus.'
        ]);
    }

    protected function authorizeBarang(Barang $barang)
    {
        $user = Auth::user();

        if (
            $user->role !== 'admin' &&
            $user->id_tempat != $barang->id_tempat
        ) {
            abort(403, 'Akses barang ditolak.');
        }
    }
}