<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('tempatSewa')
            ->select([
                'id_user',
                'id_tempat',
                'nama',
                'email',
                'role',
                'status',
                'created_at'
            ]);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query
                ->latest('id_user')
                ->paginate(30)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_tempat' => ['nullable', 'integer'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => [
                'required',
                'in:free_user,admin,owner,developer'
            ],
            'status' => [
                'required',
                'in:aktif,nonaktif'
            ],
        ]);

        $validated['password'] =
            Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => $user
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'data' => $user->load('tempatSewa')
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'id_tempat' => ['sometimes', 'nullable', 'integer'],
            'nama' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                'unique:users,email,' . $user->id_user . ',id_user'
            ],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => [
                'sometimes',
                'in:free_user,admin,owner,developer'
            ],
            'status' => [
                'sometimes',
                'in:aktif,nonaktif'
            ],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] =
                Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => $user->fresh()
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id_user === auth()->id()) {
            return response()->json([
                'message' => 'Akun yang sedang digunakan tidak dapat dihapus.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.'
        ]);
    }
}