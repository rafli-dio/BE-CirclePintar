<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\MaterialStudent;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserBadge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    /**
     * Submit jawaban kuis, hitung skor, beri XP, dan cek badge otomatis.
     *
     * POST /api/quizzes/{quiz}/submit
     *
     * Body:
     * {
     *   "answers": [
     *     { "question_id": 1, "answer": "A" },
     *     { "question_id": 2, "answer": "C" },
     *     ...
     *   ]
     * }
     */
    public function submit(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'answers'                  => ['required', 'array'],
            'answers.*.question_id'    => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer'         => ['required', 'in:A,B,C,D'],
        ]);

        // Ambil semua soal kuis beserta kunci jawaban
        $questions = $quiz->questions()
            ->makeVisible('correct_key')  // izinkan akses correct_key untuk penilaian
            ->get()
            ->keyBy('id');

        $totalQuestions = $questions->count();

        if ($totalQuestions === 0) {
            return response()->json([
                'message' => 'Kuis ini belum memiliki soal.',
            ], 422);
        }

        // Hitung jumlah jawaban benar
        $correctCount = 0;
        $answeredKeys = collect($validated['answers'])->keyBy('question_id');

        foreach ($questions as $id => $question) {
            $submitted = $answeredKeys->get($id);
            if ($submitted && strtoupper($submitted['answer']) === $question->correct_key) {
                $correctCount++;
            }
        }

        // Hitung skor (skala 0-100)
        $score = (int) round(($correctCount / $totalQuestions) * 100);

        // Tentukan XP yang diperoleh (hanya jika lulus: skor >= 70)
        $earnedXp = $score >= 70 ? $quiz->reward_xp : 0;

        // Simpan attempt dan update XP dalam satu transaksi
        $attempt = DB::transaction(function () use ($quiz, $request, $score, $earnedXp) {
            $user = $request->user();

            $attempt = QuizAttempt::create([
                'quiz_id'   => $quiz->id,
                'user_id'   => $user->id,
                'score'     => $score,
                'earned_xp' => $earnedXp,
            ]);

            // Tambahkan XP ke total_xp user
            if ($earnedXp > 0) {
                $user->increment('total_xp', $earnedXp);
                $user->refresh();
            }

            return $attempt;
        });

        // Cek dan berikan badge secara otomatis
        $newBadges = $this->checkAndAwardBadges($request->user()->fresh(), $score);

        return response()->json([
            'message'        => 'Kuis berhasil dikerjakan.',
            'data'           => [
                'attempt'        => $attempt->load('quiz:id,title,reward_xp'),
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'score'          => $score,
                'earned_xp'      => $earnedXp,
                'total_xp'       => $request->user()->fresh()->total_xp,
                'new_badges'     => $newBadges,
            ],
        ]);
    }

    /**
     * Tampilkan riwayat pengerjaan kuis oleh siswa yang sedang login.
     *
     * GET /api/my-attempts
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $attempts = $request->user()
            ->quizAttempts()
            ->with('quiz:id,title,course_id,reward_xp')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat pengerjaan kuis berhasil diambil.',
            'data'    => $attempts,
        ]);
    }

    /**
     * Tampilkan semua riwayat attempt untuk satu kuis.
     * (Untuk teacher / admin melihat nilai siswa)
     *
     * GET /api/quizzes/{quiz}/attempts
     */
    public function quizAttempts(Quiz $quiz): JsonResponse
    {
        $attempts = $quiz->attempts()
            ->with('student:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat nilai kuis berhasil diambil.',
            'data'    => $attempts,
        ]);
    }

    // ─── Private: Badge Award Logic ──────────────────────────────────────────────

    /**
     * Cek semua badge yang belum dimiliki user dan berikan jika syarat terpenuhi.
     *
     * @return \Illuminate\Support\Collection  Daftar badge baru yang diraih
     */
    private function checkAndAwardBadges($user, int $latestScore): \Illuminate\Support\Collection
    {
        // Ambil ID badge yang sudah dimiliki user
        $ownedBadgeIds = $user->earnedBadges()->pluck('badges.id');

        // Ambil semua badge yang belum dimiliki
        $unownedBadges = Badge::whereNotIn('id', $ownedBadgeIds)->get();

        $newBadges = collect();

        foreach ($unownedBadges as $badge) {
            $qualified = match ($badge->badge_type) {
                // Skor kuis terbaru >= threshold
                Badge::TYPE_QUIZ_SCORE => $latestScore >= $badge->requirement_value,

                // Total XP sudah memenuhi milestone
                Badge::TYPE_XP_MILESTONE => $user->total_xp >= $badge->requirement_value,

                // Jumlah kelas yang sudah 100% selesai >= threshold
                Badge::TYPE_COURSE_COMPLETE => $this->countCompletedCourses($user) >= $badge->requirement_value,

                default => false,
            };

            if ($qualified) {
                UserBadge::create([
                    'user_id'   => $user->id,
                    'badge_id'  => $badge->id,
                    'earned_at' => now(),
                ]);

                // Tambahkan bonus XP dari badge jika ada
                if ($badge->reward_xp > 0) {
                    $user->increment('total_xp', $badge->reward_xp);
                }

                $newBadges->push($badge);
            }
        }

        return $newBadges;
    }

    /**
     * Hitung jumlah kelas yang telah 100% diselesaikan oleh user.
     */
    private function countCompletedCourses($user): int
    {
        $enrolledCourses = $user->enrolledCourses()->with(['modules.materials'])->get();

        return $enrolledCourses->filter(function ($course) use ($user) {
            $materialIds = $course->modules->pluck('materials')->flatten()->pluck('id');
            $total = $materialIds->count();

            if ($total === 0) return false;

            $completed = MaterialStudent::where('user_id', $user->id)
                ->whereIn('material_id', $materialIds)
                ->where('is_completed', true)
                ->count();

            return $completed === $total;
        })->count();
    }
}
