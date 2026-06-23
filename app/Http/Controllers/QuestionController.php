<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Tampilkan semua soal dalam sebuah kuis.
     * correct_key disembunyikan otomatis oleh model.
     *
     * GET /api/quizzes/{quiz}/questions
     */
    public function index(Quiz $quiz): JsonResponse
    {
        $questions = $quiz->questions()->get();

        return response()->json([
            'message' => 'Daftar soal berhasil diambil.',
            'data'    => $questions,
        ]);
    }

    /**
     * Tambah soal baru ke dalam kuis.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * POST /api/quizzes/{quiz}/questions
     */
    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('addQuestion', $quiz);

        $validated = $request->validate([
            'question'    => ['required', 'string'],
            'option_a'    => ['required', 'string', 'max:255'],
            'option_b'    => ['required', 'string', 'max:255'],
            'option_c'    => ['required', 'string', 'max:255'],
            'option_d'    => ['required', 'string', 'max:255'],
            'correct_key' => ['required', 'in:A,B,C,D'],
        ]);

        $validated['quiz_id'] = $quiz->id;

        $question = Question::create($validated);

        return response()->json([
            'message' => 'Soal berhasil ditambahkan.',
            'data'    => $question->makeHidden('correct_key'),
        ], 201);
    }

    /**
     * Tampilkan detail satu soal (dengan correct_key — khusus admin/teacher).
     *
     * GET /api/questions/{question}
     */
    public function show(Question $question): JsonResponse
    {
        return response()->json([
            'message' => 'Detail soal berhasil diambil.',
            'data'    => $question->makeVisible('correct_key'),
        ]);
    }

    /**
     * Perbarui data soal.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * PUT /api/questions/{question}
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'question'    => ['sometimes', 'string'],
            'option_a'    => ['sometimes', 'string', 'max:255'],
            'option_b'    => ['sometimes', 'string', 'max:255'],
            'option_c'    => ['sometimes', 'string', 'max:255'],
            'option_d'    => ['sometimes', 'string', 'max:255'],
            'correct_key' => ['sometimes', 'in:A,B,C,D'],
        ]);

        $question->update($validated);

        return response()->json([
            'message' => 'Soal berhasil diperbarui.',
            'data'    => $question->makeVisible('correct_key'),
        ]);
    }

    /**
     * Hapus soal.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * DELETE /api/questions/{question}
     */
    public function destroy(Question $question): JsonResponse
    {
        $this->authorize('delete', $question);

        $question->delete();

        return response()->json([
            'message' => 'Soal berhasil dihapus.',
        ]);
    }
}
