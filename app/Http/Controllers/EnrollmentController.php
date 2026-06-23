<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Daftarkan siswa yang sedang login ke sebuah kelas.
     *
     * POST /api/courses/{course}/enroll
     */
    public function enroll(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        // Cek apakah sudah terdaftar
        $alreadyEnrolled = CourseStudent::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'Anda sudah terdaftar di kelas ini.',
            ], 409);
        }

        $enrollment = CourseStudent::create([
            'course_id'   => $course->id,
            'user_id'     => $user->id,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Berhasil mendaftar ke kelas.',
            'data'    => $enrollment->load('course:id,title,thumbnail'),
        ], 201);
    }

    /**
     * Batalkan pendaftaran siswa dari sebuah kelas.
     *
     * DELETE /api/courses/{course}/enroll
     */
    public function unenroll(Request $request, Course $course): JsonResponse
    {
        $deleted = CourseStudent::where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Anda tidak terdaftar di kelas ini.',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil membatalkan pendaftaran kelas.',
        ]);
    }

    /**
     * Tampilkan semua kelas yang diikuti oleh siswa yang sedang login.
     *
     * GET /api/my-courses
     */
    public function myCourses(Request $request): JsonResponse
    {
        $courses = $request->user()
            ->enrolledCourses()
            ->with(['teacher:id,name', 'category:id,name'])
            ->withPivot('enrolled_at')
            ->latest('course_student.enrolled_at')
            ->get();

        return response()->json([
            'message' => 'Daftar kelas saya berhasil diambil.',
            'data'    => $courses,
        ]);
    }

    /**
     * Tampilkan semua siswa yang terdaftar di sebuah kelas.
     * (Untuk teacher / admin)
     *
     * GET /api/courses/{course}/students
     */
    public function courseStudents(Course $course): JsonResponse
    {
        // Guru hanya bisa melihat siswa di kelas miliknya sendiri
        $this->authorize('viewStudents', $course);

        $students = $course->students()
            ->select('users.id', 'users.name', 'users.email')
            ->withPivot('enrolled_at')
            ->latest('course_student.enrolled_at')
            ->get();

        return response()->json([
            'message' => 'Daftar siswa di kelas berhasil diambil.',
            'data'    => $students,
        ]);
    }
}
