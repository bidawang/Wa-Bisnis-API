<?php

namespace App\Http\Controllers;

use App\Models\TempatSewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TempatSewaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = TempatSewa::query();

        if ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_tempat')->paginate(20)
        ]);
    }

    public function publicIndex()
    {
        return response()->json([
            'data' => TempatSewa::where('status', 'aktif')
                ->latest('id_tempat')
                ->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user'   => ['nullable', 'exists:users,id_user'],
            'nama_tempat' => ['required', 'string', 'max:255'],
            'alamat'    => ['nullable', 'string'],
            'no_hp'     => ['nullable', 'string', 'max:30'],
            'status'    => ['required', 'in:aktif,nonaktif'],
        ]);

        if ($request->user()->role !== 'developer') {
            $validated['id_user'] = $request->user()->id_user;
        }

        $tempat = TempatSewa::create($validated);

        return response()->json([
            'message' => 'Tempat sewa berhasil dibuat.',
            'data' => $tempat
        ], 201);
    }

    public function show(TempatSewa $tempatSewa)
    {
        $this->authorizeTempat($tempatSewa);

        return response()->json([
            'data' => $tempatSewa->load([
                'barang',
                'booking',
                'sewa'
            ])
        ]);
    }

    public function publicShow(TempatSewa $tempatSewa)
    {
        if ($tempatSewa->status !== 'aktif') {
            return response()->json([
                'message' => 'Tempat sewa tidak tersedia.'
            ], 404);
        }

        return response()->json([
            'data' => $tempatSewa
        ]);
    }

    public function update(Request $request, TempatSewa $tempatSewa)
    {
        $this->authorizeTempat($tempatSewa);

        $validated = $request->validate([
            'nama_tempat' => ['sometimes', 'string', 'max:255'],
            'alamat'      => ['sometimes', 'nullable', 'string'],
            'no_hp'       => ['sometimes', 'nullable', 'string', 'max:30'],
            'status'      => ['sometimes', 'in:aktif,nonaktif'],
        ]);

        $tempatSewa->update($validated);

        return response()->json([
            'message' => 'Tempat sewa berhasil diperbarui.',
            'data' => $tempatSewa->fresh()
        ]);
    }

    public function destroy(TempatSewa $tempatSewa)
    {
        $this->authorizeTempat($tempatSewa);

        $tempatSewa->delete();

        return response()->json([
            'message' => 'Tempat sewa berhasil dihapus.'
        ]);
    }

    protected function authorizeTempat(TempatSewa $tempat)
    {
        $user = Auth::user();

        if (
            $user->role !== 'developer' &&
            $user->id_tempat !== $tempat->id_tempat
        ) {
            abort(403, 'Akses tempat sewa ditolak.');
        }
    }
}