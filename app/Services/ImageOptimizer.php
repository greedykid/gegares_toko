<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Downscales and re-encodes uploads before they hit disk.
 *
 * Uploads used to be stored verbatim, so a 2048x2048 phone photo was served to
 * a 137px thumbnail slot — roughly 1.3 MB of wasted transfer on the home page.
 * Built on GD (already present) rather than pulling in an image package.
 */
class ImageOptimizer
{
    /**
     * Longest-edge budget per upload directory, sized at ~2x the largest slot
     * the image is displayed in so it still looks sharp on retina screens.
     */
    public const MAX_EDGE = [
        'products' => 800,
        'products/gallery' => 1200,
        'categories' => 400,
        'avatars' => 256,
        'reviews' => 1200,
        'settings/gallery' => 1600,
        'settings/payment_logos' => 320,
    ];

    public const DEFAULT_MAX_EDGE = 1200;

    public const QUALITY = 82;

    /**
     * Store an upload as an optimised WebP and return its path on the disk.
     *
     * Falls back to storing the original when the file cannot be processed
     * (SVG, animated GIF, corrupt data), so an upload never fails outright
     * just because it could not be optimised.
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $maxEdge = self::MAX_EDGE[$directory] ?? self::DEFAULT_MAX_EDGE;

        try {
            $webp = $this->encodeWebp($file->getRealPath(), $maxEdge);

            if ($webp !== null) {
                $path = rtrim($directory, '/') . '/' . Str::random(40) . '.webp';
                Storage::disk($disk)->put($path, $webp);

                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning("ImageOptimizer: falling back to raw store for {$directory}: " . $e->getMessage());
        }

        return $file->store($directory, $disk);
    }

    /**
     * Re-encode a file already on disk, in place, keeping its extension so any
     * stored path stays valid. Returns the new byte size, or null if skipped.
     */
    public function optimizeInPlace(string $absolutePath, int $maxEdge): ?int
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        // Only rewrite images that are actually oversized. Re-encoding a file
        // that already fits would recompress JPEG/WebP lossily on every run,
        // so repeated invocations would visibly degrade the same image.
        $edge = $this->longestEdge($absolutePath);
        if ($edge === null || $edge <= $maxEdge) {
            return null;
        }

        // Re-encode in the format the bytes actually are, not the one the
        // filename claims. Some uploads are JPEGs saved as ".png"; encoding
        // those as PNG makes them larger, so they would be skipped entirely.
        $format = $this->formatOf($absolutePath);
        if ($format === null) {
            return null;
        }

        $image = $this->load($absolutePath);
        if ($image === null) {
            return null;
        }

        $resized = $this->resize($image, $maxEdge);
        $encoded = $this->encode($resized, $format);
        imagedestroy($resized);

        if ($encoded === null) {
            return null;
        }

        // Never make a file bigger than it already was.
        if (strlen($encoded) >= filesize($absolutePath)) {
            return null;
        }

        file_put_contents($absolutePath, $encoded);

        return strlen($encoded);
    }

    /**
     * Longest edge of an image file, or null when unreadable.
     */
    public function longestEdge(string $absolutePath): ?int
    {
        $size = @getimagesize($absolutePath);

        return $size ? max($size[0], $size[1]) : null;
    }

    /**
     * The encoder to use, chosen from the file's real content type.
     */
    protected function formatOf(string $absolutePath): ?string
    {
        $info = @getimagesize($absolutePath);

        return match ($info['mime'] ?? null) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
    }

    protected function encodeWebp(string $absolutePath, int $maxEdge): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }

        $image = $this->load($absolutePath);
        if ($image === null) {
            return null;
        }

        $resized = $this->resize($image, $maxEdge);
        $encoded = $this->encode($resized, 'webp');
        imagedestroy($resized);

        return $encoded;
    }

    protected function load(string $absolutePath): ?\GdImage
    {
        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Scale so the longest edge fits the budget. Returns the source untouched
     * when it is already small enough.
     */
    protected function resize(\GdImage $source, int $maxEdge): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if (max($width, $height) <= $maxEdge) {
            return $source;
        }

        $scale = $maxEdge / max($width, $height);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($newWidth, $newHeight);

        // Without these, PNG/WebP transparency is flattened to black.
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));

        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        return $target;
    }

    protected function encode(\GdImage $image, string $extension): ?string
    {
        // Must be set on every encode path, not just after a resize: an image
        // that needed no downscaling would otherwise be written without its
        // alpha channel, silently flattening transparent logos.
        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();

        $ok = match ($extension) {
            'webp' => function_exists('imagewebp') && imagewebp($image, null, self::QUALITY),
            'png' => imagepng($image, null, 8),
            'jpg', 'jpeg' => imagejpeg($image, null, self::QUALITY),
            default => false,
        };

        $data = ob_get_clean();

        return $ok && $data !== '' ? $data : null;
    }
}
