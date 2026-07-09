<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Tampilkan data agregat statistik untuk Admin Dashboard.
     * Endpoint: GET /api/dashboard/stats
     */
    public function stats(Request $request): JsonResponse
    {
        // 1. Hitung total berdasarkan tipe
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalCourses = Course::count();
        $totalCategories = Category::count();
        $totalBadges = Badge::count();

        // 2. Data Grafik Pertumbuhan Siswa (6 bulan terakhir)
        // Format: [ { name: 'Jan', students: 10 }, { name: 'Feb', students: 15 } ]
        $chartData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            // Mundur $i bulan dari bulan sekarang
            $targetMonth = Carbon::now()->subMonths($i);
            
            // Hitung siswa yang mendaftar pada bulan dan tahun tersebut
            $studentCount = User::where('role', 'student')
                ->whereYear('created_at', $targetMonth->year)
                ->whereMonth('created_at', $targetMonth->month)
                ->count();
                
            $chartData[] = [
                'name' => $targetMonth->translatedFormat('M'), // 'Jan', 'Feb', 'Mar' (dalam bahasa lokal/inggris tergantung config)
                'students' => $studentCount
            ];
        }

        // 3. Return JSON Terstruktur
        return response()->json([
            'message' => 'Data statistik dashboard berhasil diambil.',
            'data' => [
                'metrics' => [
                    'students' => $totalStudents,
                    'teachers' => $totalTeachers,
                    'courses' => $totalCourses,
                    'categories' => $totalCategories,
                    'badges' => $totalBadges
                ],
                'growth_chart' => $chartData
            ]
        ]);
    }
}
