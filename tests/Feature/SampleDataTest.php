<?php

use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Models\BankAccount;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleDataSeeder;
use Database\Seeders\SampleProductSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

const SUPABASE_URL = 'postgresql://postgres.abc:secret@aws-0-ap-northeast-1.pooler.supabase.com:5432/postgres';

function pretendEnvironment(string $environment): void
{
    app()->detectEnvironment(fn (): string => $environment);
}

function pretendDatabaseUrl(?string $url): void
{
    config(['database.connections.'.config('database.default').'.url' => $url]);
}

describe('seeding sample data', function () {
    it('refuses outside APP_ENV=local and says why', function () {
        $this->artisan('db:seed', ['--class' => SampleDataSeeder::class])
            ->expectsOutputToContain('Data contoh TIDAK diisi: APP_ENV bukan "local"')
            ->expectsOutputToContain('SEED_SAMPLE_DATA=true')
            ->assertSuccessful();

        expect(Destination::count())->toBe(0)->and(LocalProduct::count())->toBe(0);
    });

    it('refuses on a Supabase database even with APP_ENV=local', function (string $url) {
        pretendEnvironment('local');
        pretendDatabaseUrl($url);

        $this->artisan('db:seed', ['--class' => SampleDataSeeder::class])
            ->expectsOutputToContain('koneksi database mengarah ke Supabase');

        expect(Destination::count())->toBe(0);
    })->with([
        'session pooler' => [SUPABASE_URL],
        'direct connection' => ['postgresql://postgres:secret@db.abcdefgh.supabase.co:5432/postgres'],
    ]);

    it('also guards the sample seeders that are called on their own', function () {
        pretendEnvironment('local');
        pretendDatabaseUrl(SUPABASE_URL);

        $this->artisan('db:seed', ['--class' => SampleProductSeeder::class])
            ->expectsOutputToContain('Data contoh TIDAK diisi');

        expect(LocalProduct::count())->toBe(0);
    });

    it('seeds a local database with APP_ENV=local', function () {
        pretendEnvironment('local');
        pretendDatabaseUrl(null);

        $this->seed(SampleDataSeeder::class);

        expect(Destination::count())->toBeGreaterThan(0)->and(LocalProduct::count())->toBe(9);
    });

    it('seeds anywhere when SEED_SAMPLE_DATA=true is set on purpose', function () {
        pretendDatabaseUrl(SUPABASE_URL);
        config(['siwapas.seed_sample_data' => true]);

        $this->seed(SampleDataSeeder::class);

        expect(LocalProduct::count())->toBe(9);
    });

    it('still creates the admin when sample data is refused', function () {
        config(['siwapas.admin.email' => 'admin@desa.test', 'siwapas.admin.password' => 'rahasia-panjang']);

        $this->seed(DatabaseSeeder::class);

        expect(User::query()->where('email', 'admin@desa.test')->value('role')?->value)->toBe('admin')
            ->and(Destination::count())->toBe(0);
    });
});

describe('siwapas:purge-sample-data', function () {
    beforeEach(function () {
        Storage::fake('public');
        config(['siwapas.seed_sample_data' => true]);
        $this->seed(SampleDataSeeder::class);

        $this->real = [
            'destination' => Destination::factory()->create(['slug' => 'curug-cikawung', 'name' => ['id' => 'Curug Cikawung']]),
            'product' => LocalProduct::factory()->create(['name' => ['id' => 'Kopi Pak Ujang'], 'maker_name' => 'Pak Ujang']),
            'event' => Event::factory()->create(['slug' => 'festival-panen', 'title' => ['id' => 'Festival Panen']]),
            'article' => Article::factory()->create(['slug' => 'panen-raya', 'title' => ['id' => 'Panen Raya']]),
            'faq' => Faq::factory()->create(['question' => ['id' => 'Di mana parkir?'], 'answer' => ['id' => 'Di balai desa.']]),
            'bank' => BankAccount::factory()->create(['bank_name' => 'BRI', 'account_number' => '0987654321']),
        ];
    });

    it('only lists the sample rows without --force', function () {
        $before = [Destination::count(), LocalProduct::count(), Event::count(), Article::count(), Faq::count(), BankAccount::count()];

        $this->artisan('siwapas:purge-sample-data')
            ->expectsOutputToContain('Dry run')
            ->expectsOutputToContain('Dapur Bu Imas (UMKM Contoh)')
            ->assertSuccessful();

        expect([Destination::count(), LocalProduct::count(), Event::count(), Article::count(), Faq::count(), BankAccount::count()])->toBe($before);
    });

    it('deletes the sample rows and their media with --force and keeps real data', function () {
        $sample = LocalProduct::query()->where('maker_name', 'like', '%Contoh%')->firstOrFail();
        $media = $sample->addMedia(UploadedFile::fake()->image('opak.jpg'))->toMediaCollection();

        $this->artisan('siwapas:purge-sample-data', ['--force' => true])->assertSuccessful();

        expect(LocalProduct::query()->pluck('id')->all())->toBe([$this->real['product']->id])
            ->and(Destination::query()->where('slug', 'like', 'contoh-%')->exists())->toBeFalse()
            ->and(Destination::query()->whereKey($this->real['destination']->id)->exists())->toBeTrue()
            ->and(Event::query()->pluck('id')->all())->toBe([$this->real['event']->id])
            ->and(Article::query()->pluck('id')->all())->toBe([$this->real['article']->id])
            ->and(Faq::query()->pluck('id')->all())->toBe([$this->real['faq']->id])
            ->and(BankAccount::query()->pluck('account_number')->all())->toBe(['0987654321'])
            ->and($media->fresh())->toBeNull();

        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
    });

    it('keeps a sample FAQ that the managers have since edited', function () {
        $edited = Faq::query()->get()->first(fn (Faq $faq): bool => $faq->getTranslation('question', 'id') === 'Bagaimana cara membayar?');
        $edited->setTranslation('answer', 'id', 'Transfer ke BRI desa lalu unggah bukti.')->save();

        $this->artisan('siwapas:purge-sample-data', ['--force' => true]);

        expect($edited->fresh())->not->toBeNull();
    });

    it('keeps a sample event that already has registrations and says so', function () {
        $event = Event::query()->where('slug', 'contoh-acara-1')->firstOrFail();
        EventRegistration::factory()->create(['event_id' => $event->id, 'order_id' => null]);

        $this->artisan('siwapas:purge-sample-data', ['--force' => true])
            ->expectsOutputToContain('sudah punya pendaftar tidak dihapus: id '.$event->id);

        expect($event->fresh())->not->toBeNull();
    });

    it('warns when no active payment method is left', function () {
        $this->real['bank']->update(['is_active' => false]);

        $this->artisan('siwapas:purge-sample-data', ['--force' => true])
            ->expectsOutputToContain('Tidak ada metode pembayaran aktif lagi');
    });
});
