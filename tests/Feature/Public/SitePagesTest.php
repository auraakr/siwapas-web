<?php

use App\Domain\Content\Models\ContactMessage;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\GalleryItem;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Content\Services\YoutubeUrl;
use Inertia\Testing\AssertableInertia as Assert;

it('reads the video id from the youtube links people paste', function (string $url) {
    expect(YoutubeUrl::idFrom($url))->toBe('dQw4w9WgXcQ');
})->with([
    'short link' => 'https://youtu.be/dQw4w9WgXcQ',
    'watch link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'watch link with a playlist first' => 'https://www.youtube.com/watch?list=PL1&v=dQw4w9WgXcQ&t=5s',
    'embed link' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'shorts link' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ',
]);

it('ignores links that are not youtube', function (?string $url) {
    expect(YoutubeUrl::idFrom($url))->toBeNull();
})->with([
    'another site' => 'https://example.com/watch?v=dQw4w9WgXcQ',
    'vimeo' => 'https://vimeo.com/12345678',
    'empty' => '',
    'null' => null,
]);

it('stores a contact message and cleans up the phone number', function () {
    $this->post('/kontak', [
        'name' => 'Rina Marlina',
        'phone' => '0812-3456-7890',
        'message' => 'Apakah bisa kunjungan rombongan 40 orang?',
    ])
        ->assertRedirect(route('contact.show'))
        ->assertSessionHas('contact_sent', true);

    expect(ContactMessage::query()->count())->toBe(1)
        ->and(ContactMessage::query()->first()->phone)->toBe('081234567890');
});

it('needs a phone number or an email so the managers can reply', function () {
    $this->post('/kontak', [
        'name' => 'Rina Marlina',
        'message' => 'Apakah bisa kunjungan rombongan 40 orang?',
    ])->assertSessionHasErrors(['phone', 'email']);

    expect(ContactMessage::query()->count())->toBe(0);
});

it('quietly drops submissions that fill the bot trap', function () {
    $this->post('/kontak', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Beli produk murah di situs kami sekarang juga',
        'website' => 'https://spam.example',
    ])->assertRedirect(route('contact.show'));

    expect(ContactMessage::query()->count())->toBe(0);
});

it('shows the contact page', function () {
    $this->get('/kontak')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/contact')
            ->where('sent', false));
});

it('lists only published destinations', function () {
    Destination::factory()->create(['type' => 'destination', 'status' => 'published']);
    Destination::factory()->create(['type' => 'destination', 'status' => 'draft']);
    Destination::factory()->create(['type' => 'activity', 'status' => 'published']);

    $this->get('/destinasi')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/destinations/index')
            ->where('tab', 'destinasi')
            ->has('destinations', 1)
            ->has('activities', 1));
});

it('opens the UMKM tab with every published product in order', function () {
    $later = LocalProduct::factory()->create(['name' => ['id' => 'Anyaman'], 'sort_order' => 2, 'is_published' => true, 'whatsapp_number' => '081234567890']);
    $first = LocalProduct::factory()->create(['name' => ['id' => 'Opak'], 'sort_order' => 1, 'is_published' => true, 'whatsapp_number' => null]);
    LocalProduct::factory()->create(['name' => ['id' => 'Tersembunyi'], 'is_published' => false]);

    $this->get('/destinasi?tab=umkm')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/destinations/index')
            ->where('tab', 'umkm')
            ->has('products', 2)
            ->where('products.0.id', $first->id)
            ->where('products.0.whatsapp_url', null)
            ->where('products.0.images', [])
            ->where('products.1.id', $later->id)
            ->where('products.1.whatsapp_url', 'https://wa.me/6281234567890'));
});

it('falls back to the destinations tab for an unknown tab', function () {
    $this->get('/destinasi?tab=apa-saja')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('tab', 'destinasi')->where('products', []));
});

it('shows a published destination and hides a draft one', function () {
    Destination::factory()->create(['slug' => 'curug-cikawung', 'type' => 'destination', 'status' => 'published']);
    Destination::factory()->create(['slug' => 'belum-siap', 'type' => 'destination', 'status' => 'draft']);

    $this->get('/destinasi/curug-cikawung')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/destinations/show')
            ->where('destination.slug', 'curug-cikawung'));

    $this->get('/destinasi/belum-siap')->assertNotFound();
});

it('shows only published gallery items that have something to display', function () {
    GalleryItem::factory()->create(['is_published' => true, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    GalleryItem::factory()->create(['is_published' => false, 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    GalleryItem::factory()->create(['is_published' => true, 'youtube_url' => null]);

    $this->get('/galeri')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/gallery')
            ->has('items', 1)
            ->where('items.0.youtube_id', 'dQw4w9WgXcQ'));
});

it('shows the village profile even before any page has been written', function () {
    $this->get('/profil-desa')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/village')
            ->has('sections', 0)
            ->has('team', 0));
});
