<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Compress and convert any uploaded image into WebP format.
     *
     * @param UploadedFile|string $file
     * @param string $directory Target storage directory inside public disk (e.g. 'users')
     * @param int $quality Compression quality (1-100, default 80 for optimal size/clarity balance)
     * @param int $maxDimension Maximum width or height in pixels (default 1200)
     * @return string Stored relative path (e.g. 'users/img_1792383013_abc.webp')
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public static function convertToWebp(
        UploadedFile|string $file,
        string $directory = 'users',
        int $quality = 80,
        int $maxDimension = 1200
    ): string {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!$realPath || !file_exists($realPath)) {
            throw new \InvalidArgumentException('File gambar tidak ditemukan atau tidak valid.');
        }

        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('File yang diunggah bukan merupakan format gambar yang valid.');
        }

        $mime = $imageInfo['mime'] ?? '';
        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];

        // Create GD resource according to source MIME type
        $srcImage = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
            'image/png' => @imagecreatefrompng($realPath),
            'image/webp' => @imagecreatefromwebp($realPath),
            'image/gif' => @imagecreatefromgif($realPath),
            'image/bmp', 'image/x-ms-bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($realPath) : null,
            default => null,
        };

        if (!$srcImage) {
            // Fallback attempt: create from binary string
            $content = file_get_contents($realPath);
            $srcImage = $content ? @imagecreatefromstring($content) : null;
        }

        if (!$srcImage) {
            throw new \RuntimeException('Tidak dapat memproses atau membaca format gambar ini.');
        }

        // Auto-orient image based on EXIF (smartphone camera orientation)
        if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
            try {
                $exif = @exif_read_data($realPath);
                if (!empty($exif['Orientation'])) {
                    $srcImage = match ($exif['Orientation']) {
                        3 => imagerotate($srcImage, 180, 0),
                        6 => imagerotate($srcImage, -90, 0),
                        8 => imagerotate($srcImage, 90, 0),
                        default => $srcImage,
                    };
                    $origWidth = imagesx($srcImage);
                    $origHeight = imagesy($srcImage);
                }
            } catch (\Throwable $e) {
                // Ignore EXIF read error, proceed with normal image
            }
        }

        // Calculate aspect ratio & resize if needed
        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        }

        // Create canvas with full alpha transparency support
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);

        // Resample with anti-aliasing
        imagecopyresampled(
            $targetImage,
            $srcImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        // Prepare target path in storage/app/public/{directory}
        $filename = 'img_' . time() . '_' . Str::random(12) . '.webp';
        $relativePath = trim($directory, '/') . '/' . $filename;
        $targetDirectory = Storage::disk('public')->path(trim($directory, '/'));

        if (!file_exists($targetDirectory)) {
            @mkdir($targetDirectory, 0755, true);
        }

        $fullTargetPath = Storage::disk('public')->path($relativePath);

        // Output optimized WebP
        $success = imagewebp($targetImage, $fullTargetPath, $quality);

        // Free memory
        imagedestroy($srcImage);
        imagedestroy($targetImage);

        if (!$success || !file_exists($fullTargetPath)) {
            throw new \RuntimeException('Gagal menyimpan file hasil konversi WebP.');
        }

        return $relativePath;
    }
}
