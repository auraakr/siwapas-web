<?php

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\TeamMember;
use App\Domain\Content\Services\SiteSettings;
use App\Domain\Payment\Services\PaymentSettings;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function profilePage(string $key, ?string $body, string $status = 'published'): Page
{
    return Page::factory()->create(['key' => $key, 'slug' => $key, 'status' => $status, 'body' => ['id' => $body]]);
}

describe('village profile', function () {
    it('sends each section that has text and hides the empty and draft ones', function () {
        profilePage('profile', '<p>Desa wisata di tepi Citarum.</p>');
        profilePage('history', '<p>Berawal dari kelompok sadar wisata.</p>');
        profilePage('management', '<p></p>');
        profilePage('potential', '<p>Tumbuh bersama warga.</p>', status: 'draft');
        profilePage('terms', '<p>Tidak termasuk profil.</p>');

        $this->get('/profil-desa')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/village')
                ->where('about.body', '<p>Desa wisata di tepi Citarum.</p>')
                ->where('history.body', '<p>Berawal dari kelompok sadar wisata.</p>')
                ->where('management', null)
                ->where('potential', null)
                ->where('vision', null)
                ->where('mission', []));
    });

    it('shows nothing invented when no page has been written', function () {
        $this->get('/profil-desa')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('about', null)
                ->where('history', null)
                ->where('vision', null)
                ->where('mission', [])
                ->where('management', null)
                ->where('potential', null)
                ->has('team', 0)
                ->where('location', null));
    });

    it('sends the vision as plain text and the mission as numbered points', function () {
        profilePage('vision', '<p>Terwujudnya Desa Pasirnanjung <strong>Geulis</strong>&nbsp;yang sejahtera</p>');
        profilePage('mission', '<ol><li>Menjaga alam</li><li><p>Menguatkan UMKM warga</p></li><li> </li></ol>');

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->where('vision', 'Terwujudnya Desa Pasirnanjung Geulis yang sejahtera')
                ->where('mission', ['Menjaga alam', 'Menguatkan UMKM warga']));
    });

    it('takes mission points from lines when the managers did not use a list', function () {
        profilePage('mission', '<p>1. Menjaga alam<br>2) Menguatkan UMKM</p><p>- Melestarikan budaya Sunda</p>');

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->where('mission', ['Menjaga alam', 'Menguatkan UMKM', 'Melestarikan budaya Sunda']));
    });

    it('lists the team under the managers', function () {
        TeamMember::factory()->create(['name' => 'Kedua', 'sort_order' => 2]);
        TeamMember::factory()->create(['name' => 'Pertama', 'sort_order' => 1]);

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->has('team', 2)
                ->where('team.0.name', 'Pertama'));
    });
});

describe('location', function () {
    it('embeds the map at the coordinates in the managers\' Maps link', function () {
        app(SiteSettings::class)->save(['contact' => [
            'address' => 'Pasirnanjung, Cimanggung',
            'maps_url' => 'https://www.google.com/maps/place/Pasirnanjung/@-6.9512345,107.8123456,15z',
        ]]);

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->where('location.address', 'Pasirnanjung, Cimanggung')
                ->where('location.embed_url', 'https://maps.google.com/maps?q=-6.9512345%2C107.8123456&z=15&output=embed')
                ->where('location.directions_url', 'https://www.google.com/maps/dir/?api=1&destination=-6.9512345%2C107.8123456'));
    });

    it('embeds the address when the Maps link is a short share link', function () {
        app(SiteSettings::class)->save(['contact' => [
            'address' => 'Pasirnanjung, Cimanggung',
            'maps_url' => 'https://maps.app.goo.gl/AbCdEf123',
        ]]);

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->where('location.embed_url', 'https://maps.google.com/maps?q=Pasirnanjung%2C%20Cimanggung&z=15&output=embed'));
    });

    it('shows the address and directions without a map when no Maps link is set', function () {
        config(['siwapas.contact.maps_url' => null]);
        app(SiteSettings::class)->save(['contact' => ['address' => 'Pasirnanjung, Cimanggung']]);

        $this->get('/profil-desa')
            ->assertInertia(fn (Assert $page) => $page
                ->where('location.embed_url', null)
                ->where('location.directions_url', 'https://www.google.com/maps/dir/?api=1&destination=Pasirnanjung%2C%20Cimanggung'));
    });
});

describe('visit page', function () {
    it('lists the active facilities with their price and the down payment share from the settings', function () {
        app(PaymentSettings::class)->setDownPaymentPercentage(40);
        Facility::factory()->create(['slug' => 'aula', 'name' => ['id' => 'Aula'], 'base_price' => 1500000, 'sort_order' => 2, 'description' => ['id' => '<p>Aula serbaguna.</p>']]);
        Facility::factory()->create(['slug' => 'saung', 'name' => ['id' => 'Saung'], 'sort_order' => 1]);
        Facility::factory()->create(['slug' => 'gudang', 'is_active' => false]);

        $this->get('/kunjungan')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/visit')
                ->where('dp_percentage', 40)
                ->has('facilities', 2)
                ->where('facilities.0.slug', 'saung')
                ->where('facilities.1.name', 'Aula')
                ->where('facilities.1.summary', 'Aula serbaguna.')
                ->where('facilities.1.base_price', 1500000)
                ->where('facilities.1.unit', 'day')
                ->where('facilities.1.image', null));
    });

    it('shows the published booking terms and hides empty or draft ones', function () {
        profilePage('terms', '<p>DP tidak dapat dikembalikan.</p>');
        profilePage('visit_info', '<p></p>');

        $this->get('/kunjungan')
            ->assertInertia(fn (Assert $page) => $page
                ->where('terms.body', '<p>DP tidak dapat dikembalikan.</p>')
                ->where('info', null));
    });

    it('shows the visit page even before any text has been written', function () {
        $this->get('/kunjungan')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('info', null)
                ->where('terms', null)
                ->where('facilities', [])
                ->where('dp_percentage', 50));
    });
});

describe('legacy page keys migration', function () {
    beforeEach(function () {
        $this->migration = require database_path('migrations/2026_11_27_133450_rename_legacy_profile_page_keys.php');
    });

    it('moves the old Indonesian keys to the keys the site reads, and back', function () {
        foreach (['identitas', 'sejarah', 'visi-misi', 'pengelola', 'potensi', 'dokumentasi'] as $old) {
            profilePage($old, null, status: 'draft');
        }

        $this->migration->up();

        expect(DB::table('pages')->orderBy('id')->pluck('slug', 'key')->all())->toBe([
            'profile' => 'profil-desa',
            'history' => 'sejarah',
            'vision' => 'visi-misi',
            'management' => 'pengelola',
            'potential' => 'potensi-desa',
            'dokumentasi' => 'dokumentasi',
        ]);

        $this->migration->down();

        expect(DB::table('pages')->orderBy('id')->pluck('slug', 'key')->all())->toBe([
            'identitas' => 'identitas',
            'sejarah' => 'sejarah',
            'visi-misi' => 'visi-misi',
            'pengelola' => 'pengelola',
            'potensi' => 'potensi',
            'dokumentasi' => 'dokumentasi',
        ]);
    });

    it('never overwrites a page that already uses the new key', function () {
        profilePage('identitas', '<p>lama</p>');
        $current = profilePage('profile', '<p>baru</p>');

        $this->migration->up();

        expect(DB::table('pages')->where('key', 'identitas')->exists())->toBeTrue()
            ->and($current->fresh()->getTranslation('body', 'id'))->toBe('<p>baru</p>');
    });
});
