<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Barang;
use App\Models\Paket;
use App\Models\Sewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::with([
            'detail',
            'user'
        ]);

        if ($user->role === 'free_user') {
            $query->where('id_user', $user->id_user);
        } elseif ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query
                ->latest('id_booking')
                ->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_tempat' => ['required', 'integer'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],

            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_barang' => ['nullable', 'integer'],
            'detail.*.id_paket' => ['nullable', 'integer'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:1'],
            'detail.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        if (
            $request->user()->role === 'free_user' &&
            $validated['id_tempat'] !== $request->user()->id_tempat
        ) {
            // Free user tidak harus mempunyai tempat.
            // Untuk booking publik, id_tempat berasal dari request.
        }

        return DB::transaction(function () use ($validated, $request) {

            $subtotal = 0;

            foreach ($validated['detail'] as $detail) {
                $subtotal +=
                    $detail['jumlah'] *
                    $detail['harga_satuan'];
            }

            $booking = Booking::create([
                'id_user' => $request->user()->id_user,
                'id_tempat' => $validated['id_tempat'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['detail'] as $detail) {
                $booking->detail()->create([
                    'id_barang' => $detail['id_barang'] ?? null,
                    'id_paket' => $detail['id_paket'] ?? null,
                    'jumlah' => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' =>
                        $detail['jumlah'] * $detail['harga_satuan'],
                    'total' =>
                        $detail['jumlah'] * $detail['harga_satuan'],
                ]);
            }

            return response()->json([
                'message' => 'Booking berhasil dibuat.',
                'data' => $booking->load('detail')
            ], 201);
        });
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        return response()->json([
            'data' => $booking->load([
                'user',
                'detail',
                'cancel',
                'sewa'
            ])
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (!in_array($booking->status, ['pending', 'diproses'])) {
            return response()->json([
                'message' => 'Booking tidak dapat diubah pada status ini.'
            ], 422);
        }

        $validated = $request->validate([
            'tanggal_mulai' => ['sometimes', 'date'],
            'tanggal_selesai' => ['sometimes', 'date'],
            'catatan' => ['sometimes', 'nullable', 'string'],
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking berhasil diperbarui.',
            'data' => $booking->fresh()
        ]);
    }

    public function destroy(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Booking yang sudah diproses tidak dapat dihapus.'
            ], 422);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking berhasil dihapus.'
        ]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (
            in_array($booking->status, [
                'selesai',
                'dibatalkan'
            ])
        ) {
            return response()->json([
                'message' => 'Booking tidak dapat dibatalkan.'
            ], 422);
        }

        $validated = $request->validate([
            'alasan' => ['nullable', 'string']
        ]);

        DB::transaction(function () use ($booking, $validated, $request) {

            $booking->update([
                'status' => 'dibatalkan'
            ]);

            $booking->cancel()->create([
                'id_user' => $request->user()->id_user,
                'alasan' => $validated['alasan'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Booking berhasil dibatalkan.',
            'data' => $booking->fresh()
        ]);
    }

    public function convertToSewa(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Hanya booking confirmed yang dapat dikonversi.'
            ], 422);
        }

        return DB::transaction(function () use ($booking) {

            $sewa = Sewa::create([
                'id_user' => $booking->id_user,
                'id_tempat' => $booking->id_tempat,
                'id_booking' => $booking->id_booking,
                'tanggal_mulai' => $booking->tanggal_mulai,
                'tanggal_selesai' => $booking->tanggal_selesai,
                'subtotal' => $booking->subtotal,
                'total' => $booking->total,
                'dibayar' => 0,
                'sisa' => $booking->total,
                'status' => 'aktif',
            ]);

            foreach ($booking->detail as $detail) {
                $sewa->detail()->create([
                    'id_barang' => $detail->id_barang,
                    'id_paket' => $detail->id_paket,
                    'jumlah' => $detail->jumlah,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                    'total' => $detail->total,
                ]);
            }

            $booking->update([
                'status' => 'disewa'
            ]);

            $booking->toSewa()->create([
                'id_sewa' => $sewa->id_sewa
            ]);

            return response()->json([
                'message' => 'Booking berhasil dikonversi menjadi sewa.',
                'data' => $sewa->load('detail')
            ], 201);
        });
    }

    protected function authorizeBooking(Booking $booking)
    {
        $user = Auth::user();

        if ($user->role === 'free_user') {
            if ($booking->id_user !== $user->id_user) {
                abort(403);
            }

            return;
        }

        if (
            $user->role !== 'developer' &&
            $booking->id_tempat !== $user->id_tempat
        ) {
            abort(403);
        }
    }
}