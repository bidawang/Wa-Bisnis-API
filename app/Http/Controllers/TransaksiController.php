<?php

namespace App\Http\Controllers;

use App\Models\Dompet;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function index(Request $request, Dompet $dompet)
    {
        $this->authorizeDompet($dompet);

        $query = $dompet->transaksi()
            ->latest('id_transaksi');

        return response()->json([
            'data' => $query->paginate(30)
        ]);
    }

    public function store(Request $request, Dompet $dompet)
    {
        $this->authorizeDompet($dompet);

        $validated = $request->validate([
            'jenis' => ['required', 'in:masuk,keluar'],
            'jumlah' => ['required', 'numeric', 'min:1'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'referensi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use (
            $validated,
            $dompet
        ) {

            $saldoSebelum = $dompet->saldo;

            if ($validated['jenis'] === 'masuk') {
                $saldoSesudah =
                    $saldoSebelum + $validated['jumlah'];
            } else {

                if ($validated['jumlah'] > $saldoSebelum) {
                    abort(422, 'Saldo dompet tidak mencukupi.');
                }

                $saldoSesudah =
                    $saldoSebelum - $validated['jumlah'];
            }

            $transaksi = $dompet->transaksi()->create([
                'id_tempat' => $dompet->id_tempat,
                'jenis' => $validated['jenis'],
                'jumlah' => $validated['jumlah'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'kategori' => $validated['kategori'] ?? null,
                'referensi' => $validated['referensi'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $dompet->update([
                'saldo' => $saldoSesudah
            ]);

            return response()->json([
                'message' => 'Transaksi dompet berhasil dibuat.',
                'data' => $transaksi
            ], 201);
        });
    }

    protected function authorizeDompet(Dompet $dompet)
    {
        $user = Auth::user();

        if (
            $user->role !== 'developer' &&
            $dompet->id_tempat !== $user->id_tempat
        ) {
            abort(403);
        }
    }
}