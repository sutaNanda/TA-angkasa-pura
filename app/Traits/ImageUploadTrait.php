<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    /**
     * Upload, resize, dan kompres gambar menggunakan PHP native GD.
     * Menyimpan sebagai JPEG terkompresi (tidak butuh package tambahan).
     * Jika intervention/image v3 tersedia, digunakan untuk konversi WebP.
     *
     * @param  UploadedFile  $file        File gambar dari $request->file(...)
     * @param  string        $folderPath  Subfolder di storage/app/public (misal: 'assets')
     * @param  int           $maxWidth    Lebar maksimum dalam pixel (default: 800px)
     * @param  int           $quality     Kualitas kompresi 0-100 (default: 80)
     * @return string                     Path relatif untuk disimpan ke database
     */
    protected function uploadAndOptimizeImage(
        UploadedFile $file,
        string $folderPath,
        int $maxWidth = 800,
        int $quality = 80
    ): string {

        // ── Coba gunakan intervention/image v3 jika tersedia ──────────────────
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            return $this->uploadWithIntervention($file, $folderPath, $maxWidth, $quality);
        }

        // ── Fallback: Native PHP GD ──────────────────────────────────────────
        return $this->uploadWithGd($file, $folderPath, $maxWidth, $quality);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upload menggunakan intervention/image v3 (WebP output).
     */
    private function uploadWithIntervention(UploadedFile $file, string $folderPath, int $maxWidth, int $quality): string
    {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $image = $manager->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $filename     = Str::uuid() . '.webp';
        $relativePath = $folderPath . '/' . $filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $this->ensureDirectoryExists(Storage::disk('public')->path($folderPath));

        $image->toWebp(quality: $quality)->save($absolutePath);

        return $relativePath;
    }

    /**
     * Upload menggunakan PHP native GD (JPEG output).
     * Tidak membutuhkan package tambahan — GD sudah bundled dengan PHP.
     */
    private function uploadWithGd(UploadedFile $file, string $folderPath, int $maxWidth, int $quality): string
    {
        $mimeType = $file->getMimeType();
        $source   = match (true) {
            str_contains($mimeType, 'jpeg') => imagecreatefromjpeg($file->getRealPath()),
            str_contains($mimeType, 'png')  => imagecreatefrompng($file->getRealPath()),
            str_contains($mimeType, 'gif')  => imagecreatefromgif($file->getRealPath()),
            str_contains($mimeType, 'webp') => imagecreatefromwebp($file->getRealPath()),
            default                          => imagecreatefromjpeg($file->getRealPath()),
        };

        if (!$source) {
            // Tidak bisa decode → simpan file asli langsung
            $ext          = $file->getClientOriginalExtension() ?: 'jpg';
            $filename     = Str::uuid() . '.' . $ext;
            $relativePath = $folderPath . '/' . $filename;
            Storage::disk('public')->putFileAs($folderPath, $file, $filename);
            return $relativePath;
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        // Scale down proporsional jika melebihi maxWidth
        if ($origW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round(($origH / $origW) * $maxWidth);
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $resized = imagecreatetruecolor($newW, $newH);

        // Pertahankan transparansi untuk PNG
        if (str_contains($mimeType, 'png')) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $filename     = Str::uuid() . '.jpg';
        $relativePath = $folderPath . '/' . $filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $this->ensureDirectoryExists(Storage::disk('public')->path($folderPath));

        imagejpeg($resized, $absolutePath, $quality);

        imagedestroy($source);
        imagedestroy($resized);

        return $relativePath;
    }

    /**
     * Hapus file gambar lama dari storage jika ada.
     */
    protected function deleteOldImage(?string $oldPath): void
    {
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
