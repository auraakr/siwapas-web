<?php

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Content\Models\Poster;
use App\Domain\Content\Models\SiteSetting;
use App\Domain\Content\Services\SiteSettings;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page even when there is no content yet', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('destinations', [])
            ->missing('activities')
            ->where('facilities', [])
            ->where('event_venues', [])
            ->where('products', [])
            ->where('posters', [])
            ->where('events', [])
            ->where('articles', [])
            ->where('faqs', [])
            ->where('video_url', null)
            ->where('images.hero', null));
});

it('lists only published destinations, featured first, and leaves activities to the destinations page', function () {
    Destination::factory()->create(['name' => ['id' => 'Biasa'], 'is_featured' => false, 'sort_order' => 1]);
    Destination::factory()->create(['name' => ['id' => 'Unggulan'], 'is_featured' => true, 'sort_order' => 2]);
    Destination::factory()->create(['name' => ['id' => 'Draf'], 'status' => PublishStatus::Draft]);
    Destination::factory()->create(['name' => ['id' => 'Arung Jeram'], 'type' => DestinationType::Activity]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('destinations', 2)
            ->where('destinations.0.name', 'Unggulan')
            ->where('destinations.1.name', 'Biasa')
            ->missing('activities'));
});

it('shows at most four destinations', function () {
    Destination::factory()->count(6)->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->has('destinations', 4));
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

it('previews at most four products, in the order the manager set', function () {
    LocalProduct::factory()->count(5)->sequence(fn ($sequence) => ['sort_order' => 5 - $sequence->index, 'is_published' => true])->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 4)
            ->where('products.0.id', LocalProduct::query()->where('sort_order', 1)->value('id')));
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
    $createdHero = ! File::exists($hero);

    // Never overwrite or delete a real photo that the village has dropped in.
    if ($createdHero) {
        File::ensureDirectoryExists($directory);
        File::put($hero, 'fake-image');
    }

    $about = collect(['jpg', 'jpeg', 'webp', 'png'])
        ->map(fn (string $extension): string => "images/landing/about.{$extension}")
        ->first(fn (string $path): bool => File::exists(public_path($path)));

    try {
        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('images.hero', asset('images/landing/hero.jpg'))
                ->where('images.about', $about ? asset($about) : null));
    } finally {
        if ($createdHero) {
            File::delete($hero);
        }
    }
});

it('fills every landing page section from the sample data seeder, without duplicating on a second run', function () {
    // Tests run with APP_ENV=testing, where sample data is only seeded on explicit request.
    config(['siwapas.seed_sample_data' => true]);
    $this->seed(SampleDataSeeder::class);
    $this->seed(SampleDataSeeder::class);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('destinations', 4)
            ->has('products', 4)
            ->has('events', 3)
            ->has('articles', 3)
            ->has('faqs', 3)
            ->has('facilities', 3));
});

it('lists posters that have an image, in order, falling back to the title for alt', function () {
    $second = Poster::factory()->create(['title' => ['id' => 'Kedua'], 'alt' => null]);
    $first = Poster::factory()->create(['title' => ['id' => 'Pertama'], 'alt' => ['id' => 'Alt pertama']]);
    Poster::factory()->create(['title' => ['id' => 'Tanpa gambar']]);

    foreach ([$first, $second] as $poster) {
        $poster->addMedia(UploadedFile::fake()->image('p.jpg'))->toMediaCollection('default');
    }

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('posters', 2)
            ->where('posters.0.title', 'Pertama')
            ->where('posters.0.alt', 'Alt pertama')
            ->where('posters.1.alt', 'Kedua'));
});

describe('posters', function () {
    it('shows only published posters, in the order the managers set, newest first among equals', function () {
        $hidden = Poster::factory()->create(['title' => ['id' => 'Disembunyikan'], 'is_published' => false]);
        $later = Poster::factory()->create(['title' => ['id' => 'Urutan dua'], 'sort_order' => 2]);
        $olderFirst = Poster::factory()->create(['title' => ['id' => 'Urutan satu lama'], 'sort_order' => 1]);
        $newerFirst = Poster::factory()->create(['title' => ['id' => 'Urutan satu baru'], 'sort_order' => 1]);

        foreach ([$hidden, $later, $olderFirst, $newerFirst] as $poster) {
            $poster->addMedia(UploadedFile::fake()->image('p.jpg'))->toMediaCollection('default');
        }

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('posters', 3)
                ->where('posters.0.title', 'Urutan satu baru')
                ->where('posters.1.title', 'Urutan satu lama')
                ->where('posters.2.title', 'Urutan dua'));
    });
});

describe('visit information and contact', function () {
    it('falls back to the configured hours, admission text and contact while nothing is set', function () {
        config(['siwapas.contact.whatsapp' => '081211112222', 'siwapas.contact.hours' => '09.00–23.00 WIB']);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('visit.hours', '09.00–23.00 WIB')
                ->where('visit.admission', 'Gratis untuk kunjungan reguler.')
                ->where('site.contact.whatsapp', '081211112222')
                ->where('site.contact.whatsapp_url', 'https://wa.me/6281211112222'));
    });

    it('uses what the managers saved under Pengaturan Situs over the .env values', function () {
        config(['siwapas.contact.whatsapp' => '081211112222', 'siwapas.contact.email' => 'lama@desa.test']);
        app(SiteSettings::class)->save([
            'contact' => ['whatsapp' => '085733334444', 'hours' => '08.00–22.00 WIB', 'email' => ''],
            'admission' => ['id' => 'HTM Rp5.000 per orang.', 'en' => 'Entry Rp5,000 per person.'],
        ]);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('visit.hours', '08.00–22.00 WIB')
                ->where('visit.admission', 'HTM Rp5.000 per orang.')
                ->where('site.contact.whatsapp', '085733334444')
                ->where('site.contact.email', 'lama@desa.test'));
    });

    it('shows the admission text in English after switching language', function () {
        app(SiteSettings::class)->save(['admission' => ['id' => 'Gratis.', 'en' => 'Free entry.']]);

        $this->withSession(['locale' => 'en'])->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('visit.admission', 'Free entry.'));
    });
});

describe('hosting your own event', function () {
    it('suggests the active facilities the managers picked, in facility order', function () {
        $stage = Facility::factory()->create(['slug' => 'panggung', 'name' => ['id' => 'Panggung'], 'sort_order' => 2]);
        $field = Facility::factory()->create(['slug' => 'lapangan', 'name' => ['id' => 'Lapangan'], 'sort_order' => 1]);
        $closed = Facility::factory()->create(['slug' => 'arena', 'is_active' => false]);
        Facility::factory()->create(['slug' => 'aula']);
        app(SiteSettings::class)->save(['event_venue_facility_ids' => [$stage->id, $field->id, $closed->id]]);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('event_venues', [
                ['slug' => 'lapangan', 'name' => 'Lapangan'],
                ['slug' => 'panggung', 'name' => 'Panggung'],
            ]));

        $this->get(route('events.index'))
            ->assertInertia(fn (Assert $page) => $page->has('event_venues', 2));
    });
});

describe('landing photos', function () {
    it('prefers the photo uploaded under Pengaturan Situs', function () {
        Storage::fake('public');
        app(SiteSettings::class)->imagesRecord()
            ->addMedia(UploadedFile::fake()->image('hero.jpg'))
            ->toMediaCollection(SiteSetting::HERO_IMAGE);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('images.hero', fn (string $url): bool => str_contains($url, 'hero.jpg')));
    });
});
