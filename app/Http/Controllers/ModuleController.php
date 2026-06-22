<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Tampilkan semua modul dalam sebuah kelas, diurutkan by order_number.
     */
    public function index(Course $course): JsonResponse
    {
        $modules = $course->modules()->with('materials')->get();

        return response()->json([
            'message' => 'Daftar modul berhasil diambil.',
            'data'    => $modules,
        ]);
    }

    /**
     * Tambah modul baru ke dalam sebuah kelas.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'integer', 'min:1'],
        ]);

        $validated['course_id'] = $course->id;

        $module = Module::create($validated);

        return response()->json([
            'message' => 'Modul berhasil dibuat.',
            'data'    => $module,
        ], 201);
    }

    /**
     * Tampilkan detail satu modul beserta materi-nya.
     */
    public function show(Course $course, Module $module): JsonResponse
    {
        $module->load('materials');

        return response()->json([
            'message' => 'Detail modul berhasil diambil.',
            'data'    => $module,
        ]);
    }

    /**
     * Perbarui data modul.
     */
    public function update(Request $request, Course $course, Module $module): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'order_number' => ['sometimes', 'integer', 'min:1'],
        ]);

        $module->update($validated);

        return response()->json([
            'message' => 'Modul berhasil diperbarui.',
            'data'    => $module,
        ]);
    }

    /**
     * Hapus modul (cascade ke materials).
     */
    public function destroy(Course $course, Module $module): JsonResponse
    {
        $module->delete();

        return response()->json([
            'message' => 'Modul berhasil dihapus.',
        ]);
    }
}
