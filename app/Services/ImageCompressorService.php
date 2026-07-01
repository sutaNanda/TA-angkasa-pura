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
        // Naikkan memory limit — GD butuh ~4x pixel count bytes per gambar
        // Contoh: foto 12MP (4000x3000) = 4000*3000*4 = ~48MB RAM hanya untuk decode
        ini_set('memory_limit', '1024M');

        // Pastikan hanya file gambar yang dikompres
        $mime = $file->getMimeType();
        if (!str_starts_with($mime, 'image/')) {
            // Jika bukan gambar (misal pdf), simpan seperti biasa
            return $file->store($directory, 'public');
        }

        // Safety check: jika file terlalu besar (>3MB), simpan langsung tanpa proses GD
        // untuk menghindari memory exhaustion di server (karena JPG 4MB bisa beresolusi 12MP yang butuh 48MB RAM saat decode)
        if ($file->getSize() > 3 * 1024 * 1024) {
            return $file->store($directory, 'public');
        }

        // Cek dimensi gambar menggunakan getimagesize (hanya baca header, sangat hemat memori)
        // untuk mencegah dekompresi gambar beresolusi raksasa (meski ukuran filenya kecil)
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo !== false) {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            // Jika resolusi lebih dari 12 Megapixels (misal 4000x3000), lewati proses GD
            // 12MP membutuhkan memori ~48MB hanya untuk base decode, belum proses lainnya.
            if ($width * $height > 12000000) {
                return $file->store($directory, 'public');
            }
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

        // Bebaskan memori secara eksplisit
        unset($image, $encodedImage);

        return $path;
    }
}
