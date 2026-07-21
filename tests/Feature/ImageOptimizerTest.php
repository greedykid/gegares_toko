<?php

namespace Tests\Feature;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    protected function makePng(int $width, int $height, bool $transparent = false): string
    {
        $image = imagecreatetruecolor($width, $height);

        if ($transparent) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 0, 0, 127));
        } else {
            imagefill($image, 0, 0, imagecolorallocate($image, 10, 200, 40));
        }

        $path = tempnam(sys_get_temp_dir(), 'img').'.png';
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    public function test_it_downscales_an_upload_to_the_directory_budget(): void
    {
        Storage::fake('public');

        $source = $this->makePng(2048, 2048);
        $optimizer = new ImageOptimizer;

        $path = $optimizer->store(new UploadedFile($source, 'big.png', 'image/png', null, true), 'products');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame(ImageOptimizer::MAX_EDGE['products'], max($size[0], $size[1]));
    }

    public function test_it_uses_a_tighter_budget_for_category_thumbnails(): void
    {
        Storage::fake('public');

        $source = $this->makePng(2048, 2048);

        $path = (new ImageOptimizer)->store(new UploadedFile($source, 'big.png', 'image/png', null, true), 'categories');

        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame(400, max($size[0], $size[1]));
    }

    public function test_it_leaves_an_image_that_is_already_small_at_its_own_size(): void
    {
        Storage::fake('public');

        $source = $this->makePng(120, 90);

        $path = (new ImageOptimizer)->store(new UploadedFile($source, 'small.png', 'image/png', null, true), 'products');

        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([120, 90], [$size[0], $size[1]]);
    }

    public function test_it_preserves_transparency_rather_than_flattening_it_to_black(): void
    {
        Storage::fake('public');

        $source = $this->makePng(1200, 1200, transparent: true);

        $path = (new ImageOptimizer)->store(new UploadedFile($source, 'logo.png', 'image/png', null, true), 'settings/payment_logos');

        $image = imagecreatefromstring(Storage::disk('public')->get($path));
        $alpha = (imagecolorat($image, 5, 5) >> 24) & 0x7F;
        imagedestroy($image);

        $this->assertSame(127, $alpha, 'Corner pixel should still be fully transparent.');
    }

    public function test_it_falls_back_to_storing_the_original_when_the_file_is_not_an_image(): void
    {
        Storage::fake('public');

        $path = (new ImageOptimizer)->store(
            UploadedFile::fake()->createWithContent('notes.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
            'products'
        );

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsNotWith('.webp', $path);
    }

    public function test_optimize_in_place_shrinks_a_file_and_keeps_its_extension(): void
    {
        $source = $this->makePng(2048, 2048);
        $before = filesize($source);

        $after = (new ImageOptimizer)->optimizeInPlace($source, 400);

        $this->assertNotNull($after);
        $this->assertLessThan($before, $after);
        $this->assertSame(400, (new ImageOptimizer)->longestEdge($source));
        $this->assertSame('png', pathinfo($source, PATHINFO_EXTENSION));

        @unlink($source);
    }

    public function test_optimize_in_place_shrinks_a_jpeg_that_is_misnamed_as_png(): void
    {
        // Real uploads in this project include photos saved with a .png name.
        // Choosing the encoder from the extension would re-encode them as PNG,
        // which is larger than the JPEG original, so nothing would shrink.
        $image = imagecreatetruecolor(1024, 1024);
        for ($x = 0; $x < 1024; $x += 4) {
            imagefilledrectangle($image, $x, 0, $x + 3, 1024, imagecolorallocate($image, $x % 255, (2 * $x) % 255, 90));
        }

        $path = tempnam(sys_get_temp_dir(), 'img').'.png';
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        $before = filesize($path);
        $after = (new ImageOptimizer)->optimizeInPlace($path, 800);

        $this->assertNotNull($after, 'A misnamed JPEG must still be optimized.');
        $this->assertLessThan($before, $after);
        $this->assertSame(800, (new ImageOptimizer)->longestEdge($path));
        $this->assertSame('image/jpeg', getimagesize($path)['mime'], 'It must stay a JPEG, not become a bloated PNG.');

        @unlink($path);
    }

    public function test_optimize_in_place_keeps_transparency_on_an_image_that_needs_no_resize(): void
    {
        // The no-resize path skips imagesavealpha() unless encode() sets it,
        // which would flatten a transparent payment logo to black.
        $source = $this->makePng(200, 200, transparent: true);

        (new ImageOptimizer)->optimizeInPlace($source, 400);

        $image = imagecreatefromstring(file_get_contents($source));
        $alpha = (imagecolorat($image, 5, 5) >> 24) & 0x7F;
        imagedestroy($image);

        $this->assertSame(127, $alpha, 'Transparency must survive a pass that does not resize.');

        @unlink($source);
    }

    public function test_optimize_in_place_is_idempotent_and_never_grows_a_file(): void
    {
        $optimizer = new ImageOptimizer;
        $source = $this->makePng(900, 900);
        $original = filesize($source);

        // First pass shrinks it; running the command twice must then be a no-op,
        // otherwise repeated deploys would keep degrading the same image.
        $first = $optimizer->optimizeInPlace($source, 400);
        $this->assertNotNull($first);
        $this->assertLessThanOrEqual($original, $first);

        $bytes = file_get_contents($source);
        $second = $optimizer->optimizeInPlace($source, 400);

        $this->assertNull($second, 'A second pass should find nothing left to save.');
        $this->assertSame($bytes, file_get_contents($source), 'File must be left byte-identical.');

        @unlink($source);
    }

    public function test_it_does_not_recompress_a_lossy_image_that_already_fits(): void
    {
        // PNG is lossless, so a PNG-only idempotency check cannot catch this:
        // re-encoding an in-budget WebP shaves a few bytes every run while
        // quietly degrading the picture each time.
        $image = imagecreatetruecolor(600, 600);
        for ($x = 0; $x < 600; $x += 3) {
            imagefilledrectangle($image, $x, 0, $x + 2, 600, imagecolorallocate($image, $x % 255, 120, (3 * $x) % 255));
        }
        $path = tempnam(sys_get_temp_dir(), 'img').'.webp';
        imagewebp($image, $path, 82);
        imagedestroy($image);

        $bytes = file_get_contents($path);

        $result = (new ImageOptimizer)->optimizeInPlace($path, 800);

        $this->assertNull($result, 'An in-budget lossy image must be left alone.');
        $this->assertSame($bytes, file_get_contents($path), 'File must be byte-identical after a no-op pass.');

        @unlink($path);
    }
}
