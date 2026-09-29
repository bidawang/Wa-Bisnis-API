<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Barang;
use App\Models\TempatSewa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tempat = $user->role === 'developer'
            ? null
            : $user->id_tempat;

        $booking = Booking::query();
        $sewa = Sewa::query();
        $tagihan = Tagihan::query();
        $pembayaran = Pembayaran::query();
        $barang = Barang::query();

        if ($tempat) {
            $booking->where('id_tempat', $tempat);
            $sewa->where('id_tempat', $tempat);
            $tagihan->where('id_tempat', $tempat);
            $pembayaran->where('id_tempat', $tempat);
            $barang->where('id_tempat', $tempat);
        }

        return response()->json([
            'data' => [
                'booking' => [
                    'total' => $booking->count(),
                    'pending' => (clone $booking)
                        ->where('status', 'pending')
                        ->count(),
                    'aktif' => (clone $booking)
                        ->where('status', 'confirmed')
                        ->count(),
                ],

                'sewa' => [
                    'total' => $sewa->count(),
                    'aktif' => (clone $sewa)
                        ->where('status', 'aktif')
                        ->count(),
                    'selesai' => (clone $sewa)
                        ->where('status', 'selesai')
                        ->count(),
                ],

                'tagihan' => [
                    'total' => $tagihan->count(),
                    'belum_lunas' => (clone $tagihan)
                        ->whereIn('status', [
                            'belum_lunas',
                            'sebagian'
                        ])
                        ->count(),
                    'nominal_sisa' => (clone $tagihan)
                        ->sum('sisa'),
                ],

                'pembayaran' => [
                    'total' => $pembayaran
                        ->where('status', 'berhasil')
                        ->sum('jumlah'),
                ],

                'barang' => [
                    'total' => $barang->count(),
                ],
            ]
        ]);
    }

    public function developer()
    {
        return response()->json([
            'data' => [
                'users' => User::count(),
                'tempat_sewa' => TempatSewa::count(),
                'barang' => Barang::count(),
                'booking' => Booking::count(),
                'sewa' => Sewa::count(),
                'tagihan' => Tagihan::count(),
                'pembayaran' => Pembayaran::sum('jumlah'),
            ]
        ]);
    }
}