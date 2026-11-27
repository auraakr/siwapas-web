<?php

namespace App\Console\Commands;

use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\Models\BankAccount;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Signature('siwapas:purge-sample-data {--force : Actually delete the rows; without it only a list is shown}')]
#[Description('List (dry run) or delete the sample "Contoh" rows and their media')]
class PurgeSampleDataCommand extends Command
{
    public function handle(): int
    {
        $connection = DB::connection();
        $this->line('Database: '.$connection->getDriverName().' '.($connection->getConfig('host') ?? $connection->getDatabaseName()));

        $entries = $this->sampleEntries();

        if ($entries === []) {
            $this->info('Tidak ada data contoh.');

            return self::SUCCESS;
        }

        $this->table(['Jenis', 'ID', 'Nama'], array_map(
            fn (array $entry): array => [$entry['type'], $entry['model']->getKey(), $entry['label']],
            $entries,
        ));

        // Registrations keep their event (restrictOnDelete); those events stay and are reported.
        $kept = array_values(array_filter(
            $entries,
            fn (array $entry): bool => $entry['model'] instanceof Event && $entry['model']->registrations()->exists(),
        ));

        if ($kept !== []) {
            $ids = array_map(fn (array $entry): string => (string) $entry['model']->getKey(), $kept);
            $this->warn('Acara contoh yang sudah punya pendaftar tidak dihapus: id '.implode(', ', $ids).'.');
        }

        $deletable = array_values(array_filter($entries, fn (array $entry): bool => ! in_array($entry, $kept, true)));

        if (! $this->option('force')) {
            $this->info('Dry run: '.count($deletable).' baris akan dihapus. Jalankan dengan --force untuk benar-benar menghapus.');

            return self::SUCCESS;
        }

        foreach ($deletable as $entry) {
            // Eloquent delete so Spatie Media Library also removes the files.
            DB::transaction(fn () => $entry['model']->delete());
        }

        $this->info(count($deletable).' baris data contoh dihapus beserta media-nya.');

        if (! BankAccount::query()->where('is_active', true)->exists()) {
            $this->warn('Tidak ada metode pembayaran aktif lagi: tambahkan rekening asli di panel admin sebelum menerima pesanan.');
        }

        return self::SUCCESS;
    }

    /**
     * Every row the sample seeders create is marked "Contoh"; the sample FAQs and bank account are
     * matched on their exact seeded content, so rows the managers have edited are left alone.
     *
     * @return list<array{type: string, model: Model, label: string}>
     */
    private function sampleEntries(): array
    {
        $entries = [];

        $add = function (string $type, iterable $models, callable $label) use (&$entries): void {
            foreach ($models as $model) {
                $entries[] = ['type' => $type, 'model' => $model, 'label' => $label($model)];
            }
        };

        $add(
            'Destinasi',
            Destination::query()->where(fn (Builder $query) => $query
                ->where('slug', 'like', 'contoh-%')
                // Translatable columns are JSON; cast to text so LIKE works on PostgreSQL and SQLite.
                ->orWhereRaw('CAST(name AS TEXT) LIKE ?', ['%Contoh%']))->orderBy('id')->get(),
            fn (Destination $destination): string => $destination->name,
        );

        $add(
            'Produk UMKM',
            LocalProduct::query()->where(fn (Builder $query) => $query
                ->where('maker_name', 'like', '%Contoh%')
                ->orWhereRaw('CAST(name AS TEXT) LIKE ?', ['%Contoh%']))->orderBy('id')->get(),
            fn (LocalProduct $product): string => $product->name.' · '.$product->maker_name,
        );

        $add(
            'Acara',
            Event::query()->where(fn (Builder $query) => $query
                ->where('slug', 'like', 'contoh-%')
                ->orWhereRaw('CAST(title AS TEXT) LIKE ?', ['%Contoh%']))->orderBy('id')->get(),
            fn (Event $event): string => $event->title,
        );

        $add(
            'Artikel',
            Article::query()->where(fn (Builder $query) => $query
                ->where('slug', 'like', 'contoh-%')
                ->orWhereRaw('CAST(title AS TEXT) LIKE ?', ['%Contoh%']))->orderBy('id')->get(),
            fn (Article $article): string => $article->title,
        );

        $add(
            'FAQ',
            Faq::query()->orderBy('id')->get()->filter(fn (Faq $faq): bool => $this->isSampleFaq($faq)),
            fn (Faq $faq): string => $faq->question,
        );

        $add(
            'Rekening',
            BankAccount::query()
                ->where('account_number', SampleDataSeeder::SAMPLE_ACCOUNT_NUMBER)
                ->where('bank_name', 'BCA')
                ->orderBy('id')
                ->get(),
            fn (BankAccount $account): string => $account->bank_name.' '.$account->account_number,
        );

        return $entries;
    }

    private function isSampleFaq(Faq $faq): bool
    {
        $question = $faq->getTranslation('question', 'id', false);

        return (SampleDataSeeder::SAMPLE_FAQS[$question] ?? null) === $faq->getTranslation('answer', 'id', false);
    }
}
