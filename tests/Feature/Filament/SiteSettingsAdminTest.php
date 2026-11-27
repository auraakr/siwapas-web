<?php

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Models\Poster;
use App\Domain\Content\Models\SiteSetting;
use App\Domain\Content\Services\SiteSettings;
use App\Domain\Payment\Models\BankAccount;
use App\Filament\Pages\SiteSettingsPage;
use App\Filament\Resources\PosterResource\Pages\ListPosters;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

describe('Pengaturan Situs', function () {
    it('saves contact, visit information and event venues', function () {
        $stage = Facility::factory()->create(['name' => ['id' => 'Panggung']]);

        Livewire::test(SiteSettingsPage::class)
            ->fillForm([
                'contact.whatsapp' => '085733334444',
                'contact.hours' => '08.00–22.00 WIB',
                'contact.address' => 'Pasirnanjung, Cimanggung, Sumedang',
                'contact.instagram' => 'https://instagram.com/pasirnanjunggeulis',
                'admission.id' => 'Gratis untuk kunjungan reguler.',
                'admission.en' => 'Free for regular visits.',
                'event_venue_facility_ids' => [(string) $stage->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SiteSettings::class);
        expect($settings->contact())->toMatchArray([
            'whatsapp' => '085733334444',
            'hours' => '08.00–22.00 WIB',
            'address' => 'Pasirnanjung, Cimanggung, Sumedang',
            'instagram' => 'https://instagram.com/pasirnanjunggeulis',
        ])
            ->and($settings->admissionFor('en'))->toBe('Free for regular visits.')
            ->and($settings->eventVenueFacilityIds())->toBe([$stage->id]);
    });

    it('validates the WhatsApp number and links', function () {
        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['contact.whatsapp' => '12345', 'contact.maps_url' => 'bukan-link'])
            ->call('save')
            ->assertHasFormErrors(['contact.whatsapp', 'contact.maps_url']);
    });

    it('falls back to .env again when a field is cleared', function () {
        config(['siwapas.contact.whatsapp' => '081211112222']);
        app(SiteSettings::class)->save(['contact' => ['whatsapp' => '085733334444']]);

        Livewire::test(SiteSettingsPage::class)
            ->assertFormSet(['contact.whatsapp' => '085733334444'])
            ->fillForm(['contact.whatsapp' => ''])
            ->call('save');

        expect(app(SiteSettings::class)->contact()['whatsapp'])->toBe('081211112222');
    });

    it('uploads the landing photos', function () {
        Storage::fake('public');

        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['hero_image' => UploadedFile::fake()->image('hero.jpg', 1600, 900)])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(app(SiteSettings::class)->imagesRecord()->getMedia(SiteSetting::HERO_IMAGE))->toHaveCount(1)
            ->and(app(SiteSettings::class)->imageUrl(SiteSetting::HERO_IMAGE))->toStartWith('/storage/')
            ->and(app(SiteSettings::class)->imageUrl(SiteSetting::ABOUT_IMAGE))->toBeNull();
    });

    it('is for admins only', function () {
        $this->actingAs(User::factory()->create());

        $this->get(SiteSettingsPage::getUrl())->assertForbidden();
    });
});

describe('posters', function () {
    it('hides a poster and reorders posters from the list', function () {
        $first = Poster::factory()->create(['sort_order' => 1]);
        $second = Poster::factory()->create(['sort_order' => 2]);

        Livewire::test(ListPosters::class)
            ->call('updateTableColumnState', 'is_published', (string) $first->getKey(), false)
            ->call('reorderTable', [(string) $second->getKey(), (string) $first->getKey()]);

        expect($first->fresh()->is_published)->toBeFalse()
            ->and($second->fresh()->sort_order)->toBeLessThan($first->fresh()->sort_order);
    });
});

it('names the WhatsApp number saved by the managers when a booking date is too close', function () {
    config(['siwapas.contact.whatsapp' => '081211112222']);
    app(SiteSettings::class)->save(['contact' => ['whatsapp' => '085733334444']]);
    BankAccount::factory()->create();
    $this->travelTo('2026-11-15 16:30:00');
    $facility = Facility::factory()->create(['capacity' => 10]);

    $this->post(route('reservations.store', $facility), [
        'check_in' => '2026-11-16',
        'check_out' => '2026-11-17',
        'guests' => 2,
        'customer_name' => 'Budi',
        'customer_phone' => '081234567890',
    ])->assertSessionHasErrors(['check_in' => 'Tanggal check-in terlalu dekat sehingga waktu untuk membayar DP secara online tidak cukup. Silakan pilih tanggal lain, atau hubungi pengelola via WhatsApp 085733334444 untuk sewa mendadak.']);
});
