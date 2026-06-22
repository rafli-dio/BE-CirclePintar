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
     */
    public function store(Request $request, Module $module): JsonResponse
    {
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
     */
    public function show(Module $module, Material $material): JsonResponse
    {
        return response()->json([
            'message' => 'Detail materi berhasil diambil.',
            'data'    => $material,
        ]);
    }

    /**
     * Perbarui data materi.
     */
    public function update(Request $request, Module $module, Material $material): JsonResponse
    {
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
     */
    public function destroy(Module $module, Material $material): JsonResponse
    {
        $material->delete();

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }
}
