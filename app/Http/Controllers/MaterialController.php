<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    // ─── Konfigurasi Upload ───────────────────────────────────────────────────────

    /** Direktori penyimpanan file PDF di disk 'public' */
    private const PDF_DIR = 'materials/pdfs';

    /** Ukuran maksimum file PDF: 50 MB */
    private const PDF_MAX_KB = 51200;

    // ─── Index ────────────────────────────────────────────────────────────────────

    /**
     * Tampilkan semua materi dalam sebuah modul, diurutkan by order_number.
     * Semua role yang sudah login bisa mengakses.
     * content_url sudah otomatis dikonversi ke URL publik oleh Accessor di Model.
     */
    public function index(Course $course, Module $module): JsonResponse
    {
        $materials = $module->materials()->orderBy('order_number')->get();

        return response()->json([
            'message' => 'Daftar materi berhasil diambil.',
            'data'    => $materials,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────────

    /**
     * Tambah materi baru ke dalam sebuah modul.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * Mendukung dua mode:
     *  1. Upload PDF  → kirim sebagai multipart/form-data dengan field `file`
     *  2. URL eksternal → kirim JSON dengan field `content_url` (video/text)
     *
     * Catatan: content_url di response sudah berupa URL publik lengkap
     * berkat Accessor di Material Model.
     */
    public function store(Request $request, Course $course, Module $module): JsonResponse
    {
        $this->authorize('addMaterial', $module);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:video,pdf,text'],
            'order_number' => ['required', 'integer', 'min:1'],

            // Upload file PDF
            'file'         => [
                'required_if:type,pdf',
                'nullable',
                'file',
                'mimes:pdf',
                'max:' . self::PDF_MAX_KB,
            ],
            // URL eksternal (video/artikel)
            'content_url'  => [
                'nullable',
                'string',
                'max:2048',
            ],
            // Konten teks panjang langsung (tipe text)
            'content_body' => [
                'nullable',
                'string',
            ],
        ], [
            'file.required_if'  => 'File PDF wajib diupload untuk tipe materi PDF.',
            'file.mimes'        => 'File harus berformat PDF.',
            'file.max'          => 'Ukuran file PDF maksimal 50 MB.',
        ]);

        // Pilih antara upload file atau URL eksternal
        [$contentUrl, $disk] = $this->resolveContent($request);

        $material = Material::create([
            'module_id'    => $module->id,
            'title'        => $validated['title'],
            'type'         => $validated['type'],
            'content_url'  => $contentUrl,
            'content_body' => $validated['content_body'] ?? null,
            'disk'         => $disk,
            'order_number' => $validated['order_number'],
        ]);

        // fresh() memastikan accessor content_url dijalankan ulang
        // sehingga response berisi URL publik lengkap (bukan path relatif)
        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data'    => $material->fresh(),
        ], 201);
    }

    // ─── Store Shallow ────────────────────────────────────────────────────────────

    /**
     * Versi shallow dari store: POST /api/modules/{module}/materials
     * Tidak memerlukan course sebagai parent di URL.
     * Digunakan oleh frontend Teacher Panel agar lebih sederhana.
     */
    public function storeShallow(Request $request, Module $module): JsonResponse
    {
        $this->authorize('addMaterial', $module);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:video,pdf,text'],
            'order_number' => ['required', 'integer', 'min:1'],
            'file'         => [
                'required_if:type,pdf',
                'nullable',
                'file',
                'mimes:pdf',
                'max:' . self::PDF_MAX_KB,
            ],
            'content_url'  => ['nullable', 'string', 'max:2048'],
            'content_body' => ['nullable', 'string'],
        ], [
            'file.required_if' => 'File PDF wajib diupload untuk tipe materi PDF.',
            'file.mimes'       => 'File harus berformat PDF.',
            'file.max'         => 'Ukuran file PDF maksimal 50 MB.',
        ]);

        [$contentUrl, $disk] = $this->resolveContent($request);

        $material = Material::create([
            'module_id'    => $module->id,
            'title'        => $validated['title'],
            'type'         => $validated['type'],
            'content_url'  => $contentUrl,
            'content_body' => $validated['content_body'] ?? null,
            'disk'         => $disk,
            'order_number' => $validated['order_number'],
        ]);

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data'    => $material->fresh(),
        ], 201);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────────

    /**
     * Tampilkan detail satu materi.
     * content_url sudah berupa URL publik lengkap via Accessor.
     */
    public function show(Material $material): JsonResponse
    {
        return response()->json([
            'message' => 'Detail materi berhasil diambil.',
            'data'    => $material,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────────

    /**
     * Perbarui data materi.
     * Hanya guru pemilik kelas induk atau super admin.
     *
     * Jika request mengandung file PDF baru → hapus file lama, upload baru.
     * Jika tidak ada file → hanya update metadata (title, order_number, dsb.).
     *
     * PENTING (PHP Limitation):
     * PHP tidak bisa membaca body multipart/form-data pada request PUT/PATCH.
     * Gunakan method spoofing dari frontend:
     *   - Kirim sebagai POST multipart/form-data
     *   - Tambahkan field `_method = PUT` di body
     * Atau kirim sebagai JSON (tanpa upload file) langsung via PUT.
     */
    public function update(Request $request, Material $material): JsonResponse
    {
        $this->authorize('update', $material);

        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'type'         => ['sometimes', 'in:video,pdf,text'],
            'order_number' => ['sometimes', 'integer', 'min:1'],
            'file'         => [
                'nullable', 'file', 'mimes:pdf',
                'max:' . self::PDF_MAX_KB,
            ],
            'content_url'  => ['nullable', 'string', 'max:2048'],
            'content_body' => ['nullable', 'string'],
        ], [
            'file.mimes' => 'File harus berformat PDF.',
            'file.max'   => 'Ukuran file PDF maksimal 50 MB.',
        ]);

        $updateData = array_filter(
            array_intersect_key($validated, array_flip(['title', 'type', 'order_number', 'content_body'])),
            fn($value) => $value !== null
        );

        // Jika ada file PDF baru yang diupload
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            // Hapus file lama jika tersimpan di local storage
            if ($material->disk === Material::DISK_LOCAL) {
                $oldPath = $material->getRawOriginal('content_url');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $updateData['content_url'] = $this->uploadPdf($request);
            $updateData['disk']        = Material::DISK_LOCAL;

        } elseif (array_key_exists('content_url', $validated) && $validated['content_url'] !== null) {
            // Ganti ke URL eksternal — hapus file lokal lama jika ada
            if ($material->disk === Material::DISK_LOCAL) {
                $oldPath = $material->getRawOriginal('content_url');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $updateData['content_url'] = $validated['content_url'];
            $updateData['disk']        = Material::DISK_EXTERNAL;
        }

        $material->update($updateData);

        // fresh() memastikan accessor content_url dijalankan ulang
        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data'    => $material->fresh(),
        ]);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────────

    /**
     * Hapus materi beserta file-nya dari storage (jika ada).
     * Hanya guru pemilik kelas induk atau super admin.
     * File dihapus otomatis oleh event `deleting` di Model.
     */
    public function destroy(Material $material): JsonResponse
    {
        $this->authorize('delete', $material);

        $material->delete();

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────────

    /**
     * Tentukan content_url dan disk berdasarkan isi request.
     *
     * @return array{string, string}  [content_url, disk]
     */
    private function resolveContent(Request $request): array
    {
        if ($request->type === Material::TYPE_PDF && $request->hasFile('file')) {
            return [$this->uploadPdf($request), Material::DISK_LOCAL];
        }

        return [$request->content_url, Material::DISK_EXTERNAL];
    }

    /**
     * Upload file PDF ke storage/app/public/materials/pdfs/
     * Nama file dibuat unik menggunakan UUID untuk menghindari tabrakan.
     *
     * @return string  Path relatif dari disk 'public', contoh: materials/pdfs/uuid.pdf
     */
    private function uploadPdf(Request $request): string
    {
        $file     = $request->file('file');
        $filename = Str::uuid() . '.pdf';

        // Simpan ke storage/app/public/materials/pdfs/{uuid}.pdf
        $path = $file->storeAs(self::PDF_DIR, $filename, 'public');

        return $path;
    }
}
