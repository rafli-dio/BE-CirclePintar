<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /** Direktori penyimpanan thumbnail di disk 'public' */
    private const THUMBNAIL_DIR = 'courses/thumbnails';

    /**
     * Tampilkan daftar semua kelas beserta relasi teacher & category.
     * Semua role yang sudah login bisa mengakses.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with(['teacher:id,name', 'category:id,name'])
            ->latest()
            ->get()
            ->map(function ($course) {
                $course->thumbnail_url = $this->resolveThumbnailUrl($course->thumbnail);
                return $course;
            });

        return response()->json([
            'message' => 'Daftar kelas berhasil diambil.',
            'data'    => $courses,
        ]);
    }

    /**
     * Simpan kelas baru.
     * Hanya teacher/super_admin (sudah dibatasi middleware).
     * Mendukung dua mode thumbnail:
     *   1. Upload file gambar (multipart/form-data dengan field `thumbnail_file`)
     *   2. URL eksternal (field `thumbnail` sebagai string URL)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string'],
            'thumbnail'      => ['nullable', 'string', 'max:2048'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // max 5MB
        ], [
            'thumbnail_file.image'  => 'File harus berupa gambar.',
            'thumbnail_file.mimes'  => 'Format gambar yang didukung: JPG, PNG, WebP.',
            'thumbnail_file.max'    => 'Ukuran gambar maksimal 5 MB.',
        ]);

        // Tentukan nilai thumbnail
        $thumbnailPath = $this->resolveThumbnail($request, $validated['thumbnail'] ?? null);

        $course = Course::create([
            'user_id'     => $request->user()->id,
            'category_id' => $validated['category_id'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'thumbnail'   => $thumbnailPath,
        ]);

        $course->thumbnail_url = $this->resolveThumbnailUrl($course->thumbnail);

        return response()->json([
            'message' => 'Kelas berhasil dibuat.',
            'data'    => $course->load(['teacher:id,name', 'category:id,name']),
        ], 201);
    }

    /**
     * Tampilkan detail satu kelas beserta modul dan materi-nya.
     * Semua role yang sudah login bisa mengakses.
     */
    public function show(Course $course): JsonResponse
    {
        $course->load([
            'teacher:id,name',
            'category:id,name',
            'modules.materials',
        ]);

        $course->thumbnail_url = $this->resolveThumbnailUrl($course->thumbnail);

        return response()->json([
            'message' => 'Detail kelas berhasil diambil.',
            'data'    => $course,
        ]);
    }

    /**
     * Perbarui data kelas.
     * Hanya guru pemilik kelas atau super admin.
     * Mendukung upload thumbnail baru atau URL eksternal.
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'category_id'    => ['sometimes', 'exists:categories,id'],
            'title'          => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'string'],
            'thumbnail'      => ['nullable', 'string', 'max:2048'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'thumbnail_file.image'  => 'File harus berupa gambar.',
            'thumbnail_file.mimes'  => 'Format gambar yang didukung: JPG, PNG, WebP.',
            'thumbnail_file.max'    => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $updateData = array_filter(
            array_intersect_key($validated, array_flip(['category_id', 'title', 'description'])),
            fn($value) => $value !== null
        );

        // Handle thumbnail upload atau URL baru
        if ($request->hasFile('thumbnail_file') && $request->file('thumbnail_file')->isValid()) {
            // Hapus thumbnail lama jika ada di storage lokal
            $this->deleteOldThumbnail($course->thumbnail);
            $updateData['thumbnail'] = $this->uploadThumbnail($request);
        } elseif ($request->has('thumbnail')) {
            // Hapus thumbnail lama jika ada di storage lokal (ganti ke URL)
            $this->deleteOldThumbnail($course->thumbnail);
            $updateData['thumbnail'] = $validated['thumbnail'];
        }

        $course->update($updateData);
        $course->thumbnail_url = $this->resolveThumbnailUrl($course->fresh()->thumbnail);

        return response()->json([
            'message' => 'Kelas berhasil diperbarui.',
            'data'    => $course->load(['teacher:id,name', 'category:id,name']),
        ]);
    }

    /**
     * Hapus kelas (cascade ke modules & materials).
     * Hanya guru pemilik kelas atau super admin.
     */
    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        // Hapus thumbnail dari storage jika ada
        $this->deleteOldThumbnail($course->thumbnail);

        $course->delete();

        return response()->json([
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────────

    /**
     * Upload file thumbnail ke storage/app/public/courses/thumbnails/
     */
    private function uploadThumbnail(Request $request): string
    {
        $file     = $request->file('thumbnail_file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs(self::THUMBNAIL_DIR, $filename, 'public');
    }

    /**
     * Hapus thumbnail lama dari storage lokal jika bukan URL eksternal.
     */
    private function deleteOldThumbnail(?string $thumbnail): void
    {
        if ($thumbnail && !str_starts_with($thumbnail, 'http')) {
            if (Storage::disk('public')->exists($thumbnail)) {
                Storage::disk('public')->delete($thumbnail);
            }
        }
    }

    /**
     * Tentukan path thumbnail dari request (upload file atau URL string).
     */
    private function resolveThumbnail(Request $request, ?string $urlFallback): ?string
    {
        if ($request->hasFile('thumbnail_file') && $request->file('thumbnail_file')->isValid()) {
            return $this->uploadThumbnail($request);
        }
        return $urlFallback;
    }

    /**
     * Ubah path storage lokal menjadi URL yang bisa diakses publik.
     * Jika sudah berupa URL eksternal, kembalikan apa adanya.
     */
    private function resolveThumbnailUrl(?string $thumbnail): ?string
    {
        if (!$thumbnail) return null;
        if (str_starts_with($thumbnail, 'http')) return $thumbnail;
        return Storage::disk('public')->url($thumbnail);
    }
}
