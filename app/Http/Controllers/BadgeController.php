<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * Tampilkan semua badge yang tersedia di sistem.
     *
     * GET /api/badges
     */
    public function index(): JsonResponse
    {
        $badges = Badge::orderBy('badge_type')->orderBy('requirement_value')->get();

        return response()->json([
            'message' => 'Daftar badge berhasil diambil.',
            'data'    => $badges,
        ]);
    }

    /**
     * Buat badge baru (admin/teacher).
     *
     * POST /api/badges
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255', 'unique:badges,name'],
            'description'        => ['required', 'string'],
            'icon'               => ['nullable', 'string', 'max:255'],
            'badge_type'         => ['required', 'in:quiz_score,xp_milestone,course_complete'],
            'requirement_value'  => ['required', 'integer', 'min:1'],
            'reward_xp'          => ['required', 'integer', 'min:0'],
        ]);

        $badge = Badge::create($validated);

        return response()->json([
            'message' => 'Badge berhasil dibuat.',
            'data'    => $badge,
        ], 201);
    }

    /**
     * Tampilkan detail satu badge.
     *
     * GET /api/badges/{badge}
     */
    public function show(Badge $badge): JsonResponse
    {
        $badge->loadCount('earnedByUsers');

        return response()->json([
            'message' => 'Detail badge berhasil diambil.',
            'data'    => $badge,
        ]);
    }

    /**
     * Perbarui data badge.
     *
     * PUT /api/badges/{badge}
     */
    public function update(Request $request, Badge $badge): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['sometimes', 'string', 'max:255', 'unique:badges,name,' . $badge->id],
            'description'        => ['sometimes', 'string'],
            'icon'               => ['nullable', 'string', 'max:255'],
            'badge_type'         => ['sometimes', 'in:quiz_score,xp_milestone,course_complete'],
            'requirement_value'  => ['sometimes', 'integer', 'min:1'],
            'reward_xp'          => ['sometimes', 'integer', 'min:0'],
        ]);

        $badge->update($validated);

        return response()->json([
            'message' => 'Badge berhasil diperbarui.',
            'data'    => $badge,
        ]);
    }

    /**
     * Hapus badge.
     *
     * DELETE /api/badges/{badge}
     */
    public function destroy(Badge $badge): JsonResponse
    {
        $badge->delete();

        return response()->json([
            'message' => 'Badge berhasil dihapus.',
        ]);
    }

    /**
     * Tampilkan semua badge yang telah diraih oleh siswa yang sedang login.
     *
     * GET /api/my-badges
     */
    public function myBadges(Request $request): JsonResponse
    {
        $badges = $request->user()
            ->earnedBadges()
            ->withPivot('earned_at')
            ->get();

        return response()->json([
            'message' => 'Badge saya berhasil diambil.',
            'data'    => $badges,
        ]);
    }
}
