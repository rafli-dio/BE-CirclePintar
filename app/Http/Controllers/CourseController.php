<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Tampilkan daftar semua kelas beserta relasi teacher & category.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with(['teacher:id,name', 'category:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar kelas berhasil diambil.',
            'data'    => $courses,
        ]);
    }

    /**
     * Simpan kelas baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'string', 'max:255'],
        ]);

        // Guru yang sedang login otomatis menjadi pembuat kelas.
        $validated['user_id'] = $request->user()->id;

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Kelas berhasil dibuat.',
            'data'    => $course->load(['teacher:id,name', 'category:id,name']),
        ], 201);
    }

    /**
     * Tampilkan detail satu kelas beserta modul dan materi-nya.
     */
    public function show(Course $course): JsonResponse
    {
        $course->load([
            'teacher:id,name',
            'category:id,name',
            'modules.materials',
        ]);

        return response()->json([
            'message' => 'Detail kelas berhasil diambil.',
            'data'    => $course,
        ]);
    }

    /**
     * Perbarui data kelas.
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'thumbnail'   => ['nullable', 'string', 'max:255'],
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Kelas berhasil diperbarui.',
            'data'    => $course->load(['teacher:id,name', 'category:id,name']),
        ]);
    }

    /**
     * Hapus kelas (cascade ke modules & materials).
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json([
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }
}
