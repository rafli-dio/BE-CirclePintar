<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar semua kategori.
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'message' => 'Daftar kategori berhasil diambil.',
            'data'    => $categories,
        ]);
    }

    /**
     * Buat kategori baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data'    => $category,
        ], 201);
    }

    /**
     * Tampilkan detail satu kategori.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data'    => $category,
        ]);
    }

    /**
     * Perbarui nama kategori.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $category,
        ]);
    }

    /**
     * Hapus kategori.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
