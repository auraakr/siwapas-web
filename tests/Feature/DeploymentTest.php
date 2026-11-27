<?php

use App\Domain\Content\Models\Poster;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Enums\UserRole;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

describe('siwapas:create-admin', function () {
    it('creates the admin from the command line', function () {
        config(['siwapas.admin.password' => 'Rahasia-Desa-2026!']);

        $this->artisan('siwapas:create-admin', ['email' => 'pengelola@desa.test', '--name' => 'Pengelola'])
            ->expectsOutputToContain('Akun admin pengelola@desa.test dibuat')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'pengelola@desa.test')->sole();
        expect($admin->role)->toBe(UserRole::Admin)
            ->and($admin->name)->toBe('Pengelola')
            ->and($admin->email_verified_at)->not->toBeNull();
    });

    it('asks for the password when ADMIN_PASSWORD is not set and promotes an existing user', function () {
        config(['siwapas.admin.password' => null]);
        $visitor = User::factory()->create(['email' => 'warga@desa.test']);

        $this->artisan('siwapas:create-admin', ['email' => 'warga@desa.test'])
            ->expectsQuestion('Password admin', 'Rahasia-Desa-2026!')
            ->expectsOutputToContain('kini menjadi admin')
            ->assertSuccessful();

        expect($visitor->fresh()->role)->toBe(UserRole::Admin)
            ->and(User::count())->toBe(1);
    });

    it('refuses an invalid email or a weak password', function () {
        config(['siwapas.admin.password' => 'pendek']);

        $this->artisan('siwapas:create-admin', ['email' => 'bukan-email'])->assertFailed();

        expect(User::count())->toBe(0);
    });
});

describe('siwapas:move-media', function () {
    beforeEach(function () {
        Storage::fake('public');
        Storage::fake('supabase_public');
        config(['media-library.disk_name' => 'public']);
        $this->poster = Poster::factory()->create();
        $this->media = $this->poster->addMedia(UploadedFile::fake()->image('poster.jpg'))->toMediaCollection('default');
    });

    it('only lists what would move without --force', function () {
        $this->artisan('siwapas:move-media', ['--to' => 'supabase_public'])
            ->expectsOutputToContain('Dry run: 1 media')
            ->assertSuccessful();

        expect($this->media->fresh()->disk)->toBe('public');
        Storage::disk('supabase_public')->assertMissing($this->media->getPathRelativeToRoot());
    });

    it('copies the files to the target disk, points the rows there and keeps the originals', function () {
        $this->artisan('siwapas:move-media', ['--to' => 'supabase_public', '--force' => true])->assertSuccessful();

        $path = $this->media->getPathRelativeToRoot();
        expect($this->media->fresh()->disk)->toBe('supabase_public');
        Storage::disk('supabase_public')->assertExists($path);
        Storage::disk('public')->assertExists($path);

        $this->artisan('siwapas:move-media', ['--to' => 'supabase_public'])
            ->expectsOutputToContain('Semua media sudah berada di disk "supabase_public"');
    });

    it('skips files that are not on this machine', function () {
        Storage::disk('public')->delete($this->media->getPathRelativeToRoot());

        $this->artisan('siwapas:move-media', ['--to' => 'supabase_public', '--force' => true])
            ->expectsOutputToContain('tidak ditemukan')
            ->assertFailed();

        expect($this->media->fresh()->disk)->toBe('public');
    });

    it('refuses a disk that is not configured', function () {
        $this->artisan('siwapas:move-media', ['--to' => 'tidak-ada'])->assertFailed();
    });
});

it('moves payment proofs to the private disk only with --force', function () {
    Storage::fake('local');
    Storage::fake('supabase_private');
    [, , $proof] = orderAwaitingVerification();
    Storage::disk('local')->put($proof->path, 'proof-bytes');

    $this->artisan('siwapas:move-proofs', ['--to' => 'supabase_private'])->expectsOutputToContain('Dry run: 1 bukti');
    expect($proof->fresh()->disk)->toBe($proof->disk);

    $this->artisan('siwapas:move-proofs', ['--to' => 'supabase_private', '--force' => true])->assertSuccessful();

    expect($proof->fresh()->disk)->toBe('supabase_private');
    Storage::disk('supabase_private')->assertExists($proof->path);
    Storage::disk('local')->assertExists($proof->path);
});

it('keeps payment proofs on the private Supabase disk and serves them through the signed link', function () {
    Storage::fake('supabase_private');
    config(['siwapas.payment.proof_disk' => 'supabase_private']);
    $order = Order::factory()->create();
    Payment::factory()->for($order)->create();

    $this->post(route('orders.proof.store', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertRedirect(route('orders.show', $order));

    $proof = $order->payments()->sole()->proofs()->sole();
    expect($proof->disk)->toBe('supabase_private')
        ->and($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification);
    Storage::disk('supabase_private')->assertExists($proof->path);

    $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);
    $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
});

it('hides the sign-up link when registration is switched off', function () {
    $this->get(route('login'))->assertInertia(fn (Assert $page) => $page->where('canRegister', true));

    config(['fortify.features' => array_values(array_filter(
        config('fortify.features'),
        fn ($feature): bool => $feature !== Features::registration(),
    ))]);

    $this->get(route('login'))->assertInertia(fn (Assert $page) => $page->where('canRegister', false));
});

it('renders default title, description and preview tags on the server for link previews when SSR is off', function () {
    config(['inertia.ssr.enabled' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<meta data-inertia property="og:title" content="Desa Wisata Pasirnanjung Geulis">', false)
        ->assertSee('<meta data-inertia name="description"', false)
        ->assertSee('<title data-inertia>Desa Wisata Pasirnanjung Geulis</title>', false);
});

describe('trusted proxies', function () {
    afterEach(fn () => TrustProxies::flushState());

    it('builds https URLs behind a trusted proxy such as Railway', function () {
        config(['siwapas.trusted_proxies' => '*', 'inertia.ssr.enabled' => false]);
        app()->getProvider(AppServiceProvider::class)->boot();

        $this->get('http://siwapas.test/', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
            ->assertSee('<meta data-inertia property="og:url" content="https://siwapas.test">', false);
    });

    it('ignores forwarded headers when no proxy is trusted', function () {
        config(['siwapas.trusted_proxies' => null, 'inertia.ssr.enabled' => false]);

        $this->get('http://siwapas.test/', ['X-Forwarded-Proto' => 'https'])
            ->assertSee('<meta data-inertia property="og:url" content="http://siwapas.test">', false);
    });
});
