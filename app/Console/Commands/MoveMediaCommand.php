<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

#[Signature('siwapas:move-media {--to= : Target disk (default: MEDIA_DISK)} {--force : Actually copy the files and update the media rows}')]
#[Description('List (dry run) or move uploaded media files to another disk, e.g. a Supabase bucket')]
class MoveMediaCommand extends Command
{
    /**
     * Media rows remember the disk they were stored on. This copies each file (and any generated
     * conversions) to the target disk and then points the row there; the original file is left in
     * place as a backup. Files missing on this machine are reported and skipped, so run it where
     * the uploads actually are. Safe to run again: rows already on the target are skipped.
     */
    public function handle(): int
    {
        $target = (string) ($this->option('to') ?: config('media-library.disk_name'));

        if (config("filesystems.disks.{$target}") === null) {
            $this->error("Disk \"{$target}\" tidak ada di config/filesystems.php.");

            return self::FAILURE;
        }

        $media = Media::query()->where('disk', '!=', $target)->orderBy('id')->get();

        if ($media->isEmpty()) {
            $this->info("Semua media sudah berada di disk \"{$target}\".");

            return self::SUCCESS;
        }

        $this->table(['ID', 'Model', 'Koleksi', 'File', 'Disk sekarang', 'Ada di mesin ini?'], $media->map(fn (Media $item): array => [
            $item->id,
            class_basename($item->model_type).' #'.$item->model_id,
            $item->collection_name,
            $item->file_name,
            $item->disk,
            Storage::disk($item->disk)->exists($item->getPathRelativeToRoot()) ? 'ya' : 'TIDAK',
        ])->all());

        if (! $this->option('force')) {
            $this->info("Dry run: {$media->count()} media akan dipindah ke \"{$target}\". Jalankan dengan --force untuk memindahkan.");

            return self::SUCCESS;
        }

        $moved = 0;

        foreach ($media as $item) {
            try {
                if ($this->copy($item, $target)) {
                    $item->forceFill(['disk' => $target, 'conversions_disk' => $target])->save();
                    $moved++;
                }
            } catch (Throwable $e) {
                $this->warn("Media #{$item->id} gagal: {$e->getMessage()}");
            }
        }

        $this->info("{$moved} dari {$media->count()} media dipindah ke \"{$target}\". File asli tidak dihapus.");

        return $moved === $media->count() ? self::SUCCESS : self::FAILURE;
    }

    private function copy(Media $item, string $target): bool
    {
        $source = Storage::disk($item->disk);
        $destination = Storage::disk($target);
        $path = $item->getPathRelativeToRoot();

        if (! $source->exists($path)) {
            $this->warn("Media #{$item->id}: file {$path} tidak ditemukan di disk \"{$item->disk}\" pada mesin ini, dilewati.");

            return false;
        }

        $paths = [$path];

        foreach (array_keys(array_filter($item->generated_conversions)) as $conversion) {
            $paths[] = $item->getPathRelativeToRoot($conversion);
        }

        foreach ($paths as $file) {
            $conversionDisk = Storage::disk($file === $path ? $item->disk : ($item->conversions_disk ?: $item->disk));

            if (! $conversionDisk->exists($file)) {
                continue;
            }

            $stream = $conversionDisk->readStream($file);

            if ($stream === null || ! $destination->writeStream($file, $stream, ['ContentType' => $item->mime_type])) {
                $this->warn("Media #{$item->id}: gagal menulis {$file} ke \"{$target}\".");

                return false;
            }
        }

        return true;
    }
}
