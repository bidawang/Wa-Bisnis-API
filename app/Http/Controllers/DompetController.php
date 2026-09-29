<?php

namespace App\Http\Controllers;

use App\Models\Dompet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DompetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Dompet::query();

        if ($user->role !== 'developer') {
            $query->where('id_tempat', $user->id_tempat);
        }

        return response()->json([
            'data' => $query->latest('id_dompet')->get()
        ]);
    }

    public function show(Request $request, Dompet $dompet)
    {
        $this->authorizeDompet($dompet);

        return response()->json([
            'data' => $dompet->load('transaksi')
        ]);
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