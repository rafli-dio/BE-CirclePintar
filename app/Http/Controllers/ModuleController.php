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
     * Semua role yang sudah login bisa mengakses.
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
     * Hanya guru pemilik kelas atau super admin.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $this->authorize('addModule', $course);

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
     * Semua role yang sudah login bisa mengakses.
     */
    public function show(Module $module): JsonResponse
    {
        $module->load('materials');

        return response()->json([
            'message' => 'Detail modul berhasil diambil.',
            'data'    => $module,
        ]);
    }

    /**
     * Perbarui data modul.
     * Hanya guru pemilik kelas induk atau super admin.
     */
    public function update(Request $request, Module $module): JsonResponse
    {
        $this->authorize('update', $module);

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
     * Hanya guru pemilik kelas induk atau super admin.
     */
    public function destroy(Module $module): JsonResponse
    {
        $this->authorize('delete', $module);

        $module->delete();

        return response()->json([
            'message' => 'Modul berhasil dihapus.',
        ]);
    }
}
