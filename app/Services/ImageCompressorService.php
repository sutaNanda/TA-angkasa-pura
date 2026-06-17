<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressorService
{
    /**
     * Mengunggah, mengubah skala, dan mengompres gambar ke format WebP.
     *
     * @param UploadedFile $file File gambar yang diunggah
     * @param string $directory Direktori tujuan di disk public (contoh: 'avatars' atau 'tickets')
     * @param int $maxWidth Lebar maksimal gambar (default: 1024)
     * @param int $quality Kualitas WebP 0-100 (default: 70)
     * @return string Path file yang berhasil disimpan
     */
    public static function upload(UploadedFile $file, string $directory, int $maxWidth = 1024, int $quality = 70): string
    {
        // Pastikan hanya file gambar yang dikompres
        $mime = $file->getMimeType();
        if (!str_starts_with($mime, 'image/')) {
            // Jika bukan gambar (misal pdf), simpan seperti biasa
            return $file->store($directory, 'public');
        }

        // Inisialisasi ImageManager dengan GD Driver
        $manager = new ImageManager(new Driver());

        // Membaca file gambar
        $image = $manager->decode($file->getPathname());

        // Ubah skala (scale down) jika lebar gambar melebihi batas maksimal,
        // dengan mempertahankan aspect ratio.
        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // Generate nama file unik dengan ekstensi .webp
        $filename = Str::uuid() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        // Encode gambar ke format webp sesuai kualitas
        $encodedImage = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: $quality));

        // Simpan file ke Storage disk 'public'
        Storage::disk('public')->put($path, $encodedImage->toString());

        return $path;
    }
}
