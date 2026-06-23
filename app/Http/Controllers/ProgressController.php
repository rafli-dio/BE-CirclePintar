<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Tandai sebuah materi sebagai SELESAI atau BELUM SELESAI.
     * Jika record belum ada, otomatis dibuat (upsert).
     *
     * POST /api/materials/{material}/progress
     */
    public function markProgress(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $completedAt = $validated['is_completed'] ? now() : null;

        // updateOrCreate: buat baru jika belum ada, update jika sudah ada
        $progress = MaterialStudent::updateOrCreate(
            [
                'material_id' => $material->id,
                'user_id'     => $request->user()->id,
            ],
            [
                'is_completed' => $validated['is_completed'],
                'completed_at' => $completedAt,
            ]
        );

        $message = $validated['is_completed']
            ? 'Materi ditandai selesai.'
            : 'Materi ditandai belum selesai.';

        return response()->json([
            'message' => $message,
            'data'    => $progress,
        ]);
    }

    /**
     * Tampilkan progres belajar siswa yang sedang login untuk satu kelas.
     * Menghitung persentase materi yang sudah selesai.
     *
     * GET /api/courses/{course}/progress
     */
    public function courseProgress(Request $request, \App\Models\Course $course): JsonResponse
    {
        $userId = $request->user()->id;

        // Ambil semua material_id dalam kelas ini (lewat modules)
        $materialIds = $course->modules()
            ->with('materials:id,module_id,title,type')
            ->get()
            ->pluck('materials')
            ->flatten()
            ->pluck('id');

        $totalMaterials = $materialIds->count();

        if ($totalMaterials === 0) {
            return response()->json([
                'message'          => 'Kelas ini belum memiliki materi.',
                'data'             => [
                    'total_materials'     => 0,
                    'completed_materials' => 0,
                    'percentage'          => 0,
                    'details'             => [],
                ],
            ]);
        }

        // Ambil record progres milik siswa untuk materi-materi ini
        $progressRecords = MaterialStudent::where('user_id', $userId)
            ->whereIn('material_id', $materialIds)
            ->get()
            ->keyBy('material_id');

        $completedCount = $progressRecords->where('is_completed', true)->count();
        $percentage     = round(($completedCount / $totalMaterials) * 100, 2);

        // Susun detail per modul
        $details = $course->modules()->with('materials:id,module_id,title,type,order_number')->get()
            ->map(function ($module) use ($progressRecords) {
                return [
                    'module_id'    => $module->id,
                    'module_title' => $module->title,
                    'materials'    => $module->materials->map(function ($material) use ($progressRecords) {
                        $record = $progressRecords->get($material->id);
                        return [
                            'material_id'   => $material->id,
                            'title'         => $material->title,
                            'type'          => $material->type,
                            'order_number'  => $material->order_number,
                            'is_completed'  => $record?->is_completed ?? false,
                            'completed_at'  => $record?->completed_at,
                        ];
                    }),
                ];
            });

        return response()->json([
            'message' => 'Progres kelas berhasil diambil.',
            'data'    => [
                'course_id'           => $course->id,
                'course_title'        => $course->title,
                'total_materials'     => $totalMaterials,
                'completed_materials' => $completedCount,
                'percentage'          => $percentage,
                'details'             => $details,
            ],
        ]);
    }

    /**
     * Tampilkan seluruh progres belajar siswa yang sedang login
     * di semua kelas yang diikutinya.
     *
     * GET /api/my-progress
     */
    public function myProgress(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrolledCourses = $user->enrolledCourses()
            ->with(['modules.materials'])
            ->get();

        $summary = $enrolledCourses->map(function ($course) use ($user) {
            $materialIds = $course->modules
                ->pluck('materials')
                ->flatten()
                ->pluck('id');

            $totalMaterials = $materialIds->count();

            $completedCount = MaterialStudent::where('user_id', $user->id)
                ->whereIn('material_id', $materialIds)
                ->where('is_completed', true)
                ->count();

            $percentage = $totalMaterials > 0
                ? round(($completedCount / $totalMaterials) * 100, 2)
                : 0;

            return [
                'course_id'           => $course->id,
                'course_title'        => $course->title,
                'thumbnail'           => $course->thumbnail,
                'enrolled_at'         => $course->pivot->enrolled_at,
                'total_materials'     => $totalMaterials,
                'completed_materials' => $completedCount,
                'percentage'          => $percentage,
            ];
        });

        return response()->json([
            'message' => 'Progres belajar saya berhasil diambil.',
            'data'    => $summary,
        ]);
    }
}
