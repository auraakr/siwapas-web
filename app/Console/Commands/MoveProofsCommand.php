<?php

namespace App\Console\Commands;

use App\Domain\Payment\Models\PaymentProof;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('siwapas:move-proofs {--to= : Target disk (default: PAYMENT_PROOF_DISK)} {--force : Actually copy the files and update the proof rows}')]
#[Description('List (dry run) or move payment proof files to another private disk, e.g. a Supabase bucket')]
class MoveProofsCommand extends Command
{
    /**
     * Each proof row remembers its disk. This copies the file to the target disk and points the
     * row there; the original stays as a backup. Files missing on this machine are skipped, so run
     * it where they were uploaded. Safe to run again.
     */
    public function handle(): int
    {
        $target = (string) ($this->option('to') ?: config('siwapas.payment.proof_disk'));

        if (config("filesystems.disks.{$target}") === null) {
            $this->error("Disk \"{$target}\" tidak ada di config/filesystems.php.");

            return self::FAILURE;
        }

        $proofs = PaymentProof::query()->where('disk', '!=', $target)->orderBy('id')->get();

        if ($proofs->isEmpty()) {
            $this->info("Semua bukti pembayaran sudah berada di disk \"{$target}\".");

            return self::SUCCESS;
        }

        $this->table(['ID', 'Pembayaran', 'File', 'Disk sekarang', 'Ada di mesin ini?'], $proofs->map(fn (PaymentProof $proof): array => [
            $proof->id,
            $proof->payment_id,
            $proof->original_name,
            $proof->disk,
            Storage::disk($proof->disk)->exists($proof->path) ? 'ya' : 'TIDAK',
        ])->all());

        if (! $this->option('force')) {
            $this->info("Dry run: {$proofs->count()} bukti akan dipindah ke \"{$target}\". Jalankan dengan --force untuk memindahkan.");

            return self::SUCCESS;
        }

        $moved = 0;

        foreach ($proofs as $proof) {
            try {
                $source = Storage::disk($proof->disk);

                if (! $source->exists($proof->path)) {
                    $this->warn("Bukti #{$proof->id}: file tidak ditemukan di disk \"{$proof->disk}\" pada mesin ini, dilewati.");

                    continue;
                }

                $stream = $source->readStream($proof->path);

                if ($stream === null || ! Storage::disk($target)->writeStream($proof->path, $stream, ['ContentType' => $proof->mime_type])) {
                    $this->warn("Bukti #{$proof->id}: gagal menulis ke \"{$target}\".");

                    continue;
                }

                $proof->forceFill(['disk' => $target])->save();
                $moved++;
            } catch (Throwable $e) {
                $this->warn("Bukti #{$proof->id} gagal: {$e->getMessage()}");
            }
        }

        $this->info("{$moved} dari {$proofs->count()} bukti dipindah ke \"{$target}\". File asli tidak dihapus.");

        return $moved === $proofs->count() ? self::SUCCESS : self::FAILURE;
    }
}
