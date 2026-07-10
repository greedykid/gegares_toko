<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Re-encodes images already sitting on the public disk, which were uploaded
 * before ImageOptimizer existed. Files are rewritten in place under their
 * original names, so every path stored in the database stays valid.
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
                            {--dry-run : Report what would change without writing anything}
                            {--backup= : Copy each original into this directory before rewriting it}';

    protected $description = 'Downscale and recompress oversized images on the public disk';

    public function handle(ImageOptimizer $optimizer): int
    {
        $root = rtrim(str_replace('\\', '/', Storage::disk('public')->path('')), '/');

        if (!is_dir($root)) {
            $this->error("Public disk not found at {$root}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $backupRoot = $this->option('backup') ? rtrim(str_replace('\\', '/', $this->option('backup')), '/') : null;
        $savedBytes = 0;
        $touched = 0;
        $rows = [];

        if ($backupRoot && !$dryRun) {
            $this->line("Backing up originals to {$backupRoot}");
        }

        foreach ($this->imageFiles($root) as $path) {
            $relative = ltrim(str_replace($root, '', $path), '/');
            $directory = trim(str_replace('\\', '/', dirname($relative)), '.');
            $maxEdge = ImageOptimizer::MAX_EDGE[$directory] ?? ImageOptimizer::DEFAULT_MAX_EDGE;

            $before = filesize($path);
            $edge = $optimizer->longestEdge($path);

            if ($edge === null) {
                continue;
            }

            if ($dryRun) {
                if ($edge > $maxEdge) {
                    $rows[] = [$relative, "{$edge}px", $this->kib($before), '→ ' . $maxEdge . 'px'];
                    $touched++;
                }

                continue;
            }

            $backupPath = null;
            $backupCreatedNow = false;

            if ($backupRoot) {
                $backupPath = "{$backupRoot}/{$relative}";

                if (!is_dir(dirname($backupPath)) && !mkdir(dirname($backupPath), 0755, true) && !is_dir(dirname($backupPath))) {
                    $this->error("Could not create backup directory for {$relative}; aborting before anything is overwritten.");

                    return self::FAILURE;
                }

                // Never clobber an existing backup: on a second run that would
                // replace the true original with an already-processed copy.
                if (!file_exists($backupPath)) {
                    if (!copy($path, $backupPath)) {
                        $this->error("Could not back up {$relative}; aborting before anything is overwritten.");

                        return self::FAILURE;
                    }

                    $backupCreatedNow = true;
                }
            }

            $after = $optimizer->optimizeInPlace($path, $maxEdge);

            // Nothing was rewritten, so the copy we just took is dead weight.
            // Only drop a backup this run created — an older one is the original.
            if ($after === null && $backupCreatedNow) {
                @unlink($backupPath);
            }

            if ($after !== null) {
                $savedBytes += $before - $after;
                $touched++;
                $rows[] = [$relative, "{$edge}px → " . $optimizer->longestEdge($path) . 'px', $this->kib($before), $this->kib($after)];
            }
        }

        if ($rows === []) {
            $this->info('Nothing to optimize — every image is already within budget.');

            return self::SUCCESS;
        }

        $this->table(
            $dryRun ? ['File', 'Current edge', 'Size', 'Would become'] : ['File', 'Edge', 'Before', 'After'],
            $rows
        );

        if ($dryRun) {
            $this->comment("{$touched} file(s) would be optimized. Re-run without --dry-run to apply.");

            return self::SUCCESS;
        }

        $this->info("Optimized {$touched} file(s), saved " . $this->kib($savedBytes) . '.');

        return self::SUCCESS;
    }

    /**
     * @return iterable<string>
     */
    protected function imageFiles(string $root): iterable
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(png|jpe?g|webp)$/i', $file->getFilename())) {
                yield str_replace('\\', '/', $file->getPathname());
            }
        }
    }

    protected function kib(int $bytes): string
    {
        return number_format($bytes / 1024, 1) . ' KiB';
    }
}
