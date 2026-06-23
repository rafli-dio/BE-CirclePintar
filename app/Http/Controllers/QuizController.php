<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Tampilkan semua kuis dalam sebuah kelas.
     * Hanya admin/teacher (sudah dibatasi middleware).
     *
     * GET /api/courses/{course}/quizzes
     */
    public function index(Course $course): JsonResponse
    {
        $quizzes = $course->quizzes()
            ->withCount('questions')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar kuis berhasil diambil.',
            'data'    => $quizzes,
        ]);
    }

    /**
     * Buat kuis baru di dalam sebuah kelas.
     * Hanya guru pemilik kelas atau super admin.
     *
     * POST /api/courses/{course}/quizzes
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $this->authorize('addQuiz', $course);

        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'time_limit' => ['required', 'integer', 'min:1', 'max:300'],
            'reward_xp'  => ['required', 'integer', 'min:0'],
        ]);

        $validated['course_id'] = $course->id;

        $quiz = Quiz::create($validated);

        return response()->json([
            'message' => 'Kuis berhasil dibuat.',
            'data'    => $quiz,
        ], 201);
    }

    /**
     * Tampilkan detail satu kuis beserta soal-soalnya.
     * correct_key disembunyikan agar tidak bocor ke siswa.
     *
     * GET /api/quizzes/{quiz}
     */
    public function show(Quiz $quiz): JsonResponse
    {
        $quiz->load(['questions', 'course:id,title']);

        return response()->json([
            'message' => 'Detail kuis berhasil diambil.',
            'data'    => $quiz,
        ]);
    }

    /**
     * Perbarui pengaturan kuis.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * PUT /api/quizzes/{quiz}
     */
    public function update(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'title'      => ['sometimes', 'string', 'max:255'],
            'time_limit' => ['sometimes', 'integer', 'min:1', 'max:300'],
            'reward_xp'  => ['sometimes', 'integer', 'min:0'],
        ]);

        $quiz->update($validated);

        return response()->json([
            'message' => 'Kuis berhasil diperbarui.',
            'data'    => $quiz,
        ]);
    }

    /**
     * Hapus kuis (cascade ke questions & attempts).
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * DELETE /api/quizzes/{quiz}
     */
    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return response()->json([
            'message' => 'Kuis berhasil dihapus.',
        ]);
    }
}
