<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Tampilkan semua materi dalam sebuah modul, diurutkan by order_number.
     * Semua role yang sudah login bisa mengakses.
     */
    public function index(Module $module): JsonResponse
    {
        $materials = $module->materials()->get();

        return response()->json([
            'message' => 'Daftar materi berhasil diambil.',
            'data'    => $materials,
        ]);
    }

    /**
     * Tambah materi baru ke dalam sebuah modul.
     * Hanya guru pemilik kelas induk atau super admin.
     */
    public function store(Request $request, Module $module): JsonResponse
    {
        $this->authorize('addMaterial', $module);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:video,pdf,text'],
            'content_url'  => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'integer', 'min:1'],
        ]);

        $validated['module_id'] = $module->id;

        $material = Material::create($validated);

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data'    => $material,
        ], 201);
    }

    /**
     * Tampilkan detail satu materi.
     * Semua role yang sudah login bisa mengakses.
     */
    public function show(Material $material): JsonResponse
    {
        return response()->json([
            'message' => 'Detail materi berhasil diambil.',
            'data'    => $material,
        ]);
    }

    /**
     * Perbarui data materi.
     * Hanya guru pemilik kelas induk atau super admin.
     */
    public function update(Request $request, Material $material): JsonResponse
    {
        $this->authorize('update', $material);

        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'type'         => ['sometimes', 'in:video,pdf,text'],
            'content_url'  => ['sometimes', 'string', 'max:255'],
            'order_number' => ['sometimes', 'integer', 'min:1'],
        ]);

        $material->update($validated);

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data'    => $material,
        ]);
    }

    /**
     * Hapus materi.
     * Hanya guru pemilik kelas induk atau super admin.
     */
    public function destroy(Material $material): JsonResponse
    {
        $this->authorize('delete', $material);

        $material->delete();

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }
}
