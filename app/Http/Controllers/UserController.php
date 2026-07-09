<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        // Ambil semua pengguna, urutkan yang terbaru
        $users = User::latest()->get();
        return response()->json([
            'message' => 'Berhasil mengambil daftar pengguna.',
            'data'    => $users
        ]);
    }

    /**
     * Menyimpan pengguna baru (Misal admin mendaftarkan guru/siswa manual).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role'     => ['required', 'in:super_admin,teacher,student'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        $user = User::create($validated);

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data'    => $user
        ], 201);
    }

    /**
     * Menampilkan detail satu pengguna.
     */
    public function show(User $user)
    {
        return response()->json([
            'message' => 'Berhasil mengambil data pengguna.',
            'data'    => $user
        ]);
    }

    /**
     * Mengupdate data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role'  => ['sometimes', 'in:super_admin,teacher,student'],
            'status'=> ['sometimes', 'in:active,inactive,suspended'],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Password::defaults()]]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'data'    => $user
        ]);
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        if ($user->role === 'super_admin') {
            return response()->json([
                'message' => 'Tidak dapat menghapus sesama Super Admin.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Pengguna berhasil dihapus.'
        ]);
    }
}
