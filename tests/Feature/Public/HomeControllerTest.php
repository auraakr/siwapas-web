<?php

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page even when there is no content yet', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('destinations', [])
            ->where('activities', [])
            ->where('facilities', [])
            ->where('products', [])
            ->where('events', [])
            ->where('articles', [])
            ->where('faqs', [])
            ->where('video_url', null)
            ->where('images.hero', null));
});

it('lists only published destinations, featured first, and keeps activities separate', function () {
    Destination::factory()->create(['name' => ['id' => 'Biasa'], 'is_featured' => false, 'sort_order' => 1]);
    Destination::factory()->create(['name' => ['id' => 'Unggulan'], 'is_featured' => true, 'sort_order' => 2]);
    Destination::factory()->create(['name' => ['id' => 'Draf'], 'status' => PublishStatus::Draft]);
    Destination::factory()->create(['name' => ['id' => 'Arung Jeram'], 'type' => DestinationType::Activity]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('destinations', 2)
            ->where('destinations.0.name', 'Unggulan')
            ->where('destinations.1.name', 'Biasa')
            ->has('activities', 1)
            ->where('activities.0.name', 'Arung Jeram'));
});

it('shows at most four destinations and three activities', function () {
    Destination::factory()->count(6)->create();
    Destination::factory()->count(5)->create(['type' => DestinationType::Activity]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->has('destinations', 4)->has('activities', 3));
});

it('shows active facilities with their starting price', function () {
    Facility::factory()->create(['name' => ['id' => 'Aula Desa'], 'base_price' => 1500000, 'is_active' => true]);
    Facility::factory()->create(['name' => ['id' => 'Gudang'], 'is_active' => false]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('facilities', 1)
            ->where('facilities.0.name', 'Aula Desa')
            ->where('facilities.0.base_price', 1500000));
});

it('shows only published products and turns the WhatsApp number into a link', function () {
    LocalProduct::factory()->create(['name' => ['id' => 'Keripik'], 'whatsapp_number' => '081234567890', 'is_published' => true]);
    LocalProduct::factory()->create(['name' => ['id' => 'Tanpa nomor'], 'whatsapp_number' => null, 'is_published' => true, 'sort_order' => 5]);
    LocalProduct::factory()->create(['name' => ['id' => 'Tersembunyi'], 'is_published' => false]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 2)
            ->where('products.0.whatsapp_url', 'https://wa.me/6281234567890')
            ->where('products.1.whatsapp_url', null));
});

it('shows the next three published events in date order and skips past or unpublished ones', function () {
    Event::factory()->create(['title' => ['id' => 'Ketiga'], 'starts_at' => now()->addDays(30)]);
    Event::factory()->create(['title' => ['id' => 'Pertama'], 'starts_at' => now()->addDays(5)]);
    Event::factory()->create(['title' => ['id' => 'Kedua'], 'starts_at' => now()->addDays(10)]);
    Event::factory()->create(['title' => ['id' => 'Keempat'], 'starts_at' => now()->addDays(40)]);
    Event::factory()->create(['title' => ['id' => 'Lewat'], 'starts_at' => now()->subDay()]);
    Event::factory()->create(['title' => ['id' => 'Draf'], 'starts_at' => now()->addDay(), 'status' => EventStatus::Draft]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events', 3)
            ->where('events.0.title', 'Pertama')
            ->where('events.1.title', 'Kedua')
            ->where('events.2.title', 'Ketiga'));
});

it('shows the latest published articles that are already due', function () {
    Article::factory()->create(['title' => ['id' => 'Lama'], 'published_at' => now()->subDays(5)]);
    Article::factory()->create(['title' => ['id' => 'Baru'], 'published_at' => now()->subDay()]);
    Article::factory()->create(['title' => ['id' => 'Terjadwal'], 'published_at' => now()->addDay()]);
    Article::factory()->create(['title' => ['id' => 'Draf'], 'published_at' => now()->subDay(), 'status' => PublishStatus::Draft]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('articles', 2)
            ->where('articles.0.title', 'Baru')
            ->where('articles.1.title', 'Lama'));
});

it('shows published FAQs in their configured order', function () {
    Faq::factory()->create(['question' => ['id' => 'Kedua?'], 'sort_order' => 2]);
    Faq::factory()->create(['question' => ['id' => 'Pertama?'], 'sort_order' => 1]);
    Faq::factory()->create(['question' => ['id' => 'Tersembunyi?'], 'is_published' => false]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('faqs', 2)
            ->where('faqs.0.question', 'Pertama?')
            ->where('faqs.1.question', 'Kedua?'));
});

it('shares the public contact details with every public page and hides empty ones', function () {
    config()->set('siwapas.contact', [
        'phone' => '022-1234567',
        'whatsapp' => '081234567890',
        'email' => 'info@example.test',
        'address' => 'Jl. Desa No. 1, Bandung',
        'hours' => null,
        'instagram' => '',
    ]);

    foreach ([route('home'), route('facilities.index'), route('events.index')] as $url) {
        $this->get($url)->assertInertia(fn (Assert $page) => $page
            ->where('site.contact.email', 'info@example.test')
            ->where('site.contact.whatsapp_url', 'https://wa.me/6281234567890')
            ->where('site.contact.maps_url', 'https://www.google.com/maps/search/?api=1&query=Jl.+Desa+No.+1%2C+Bandung')
            ->missing('site.contact.hours')
            ->missing('site.contact.instagram'));
    }
});

it('uses a configured maps link instead of building one from the address', function () {
    config()->set('siwapas.contact', ['address' => 'Jl. Desa', 'maps_url' => 'https://maps.example.test/desa']);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('site.contact.maps_url', 'https://maps.example.test/desa'));
});

it('passes the hero video link only when it is configured', function () {
    config()->set('siwapas.landing.video_url', 'https://www.youtube.com/watch?v=abc');

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('video_url', 'https://www.youtube.com/watch?v=abc'));
});

it('uses photos dropped into public/images/landing', function () {
    $directory = public_path('images/landing');
    $hero = $directory.'/hero.jpg';

    File::ensureDirectoryExists($directory);
    File::put($hero, 'fake-image');

    try {
        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('images.hero', asset('images/landing/hero.jpg'))
                ->where('images.about', null));
    } finally {
        File::delete($hero);
    }
});

it('fills every landing page section from the sample data seeder, without duplicating on a second run', function () {
    $this->seed(SampleDataSeeder::class);
    $this->seed(SampleDataSeeder::class);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('destinations', 4)
            ->has('activities', 3)
            ->has('products', 3)
            ->has('events', 3)
            ->has('articles', 3)
            ->has('faqs', 3)
            ->has('facilities', 2));
});
