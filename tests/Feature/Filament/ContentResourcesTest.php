<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\BlockedDate;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Catalog\Models\PricingRule;
use App\Domain\Content\Enums\ArticleCategory;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Filament\Resources\BlockedDateResource\Pages\ManageBlockedDates;
use App\Filament\Resources\DestinationResource\Pages\CreateDestination;
use App\Filament\Resources\DestinationResource\Pages\EditDestination;
use App\Filament\Resources\DestinationResource\Pages\ListDestinations;
use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Filament\Resources\EventResource\RelationManagers\RegistrationsRelationManager;
use App\Filament\Resources\FacilityResource\Pages\CreateFacility;
use App\Filament\Resources\FacilityResource\Pages\ListFacilities;
use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Filament\Resources\LocalProductResource\Pages\CreateLocalProduct;
use App\Filament\Resources\PricingRuleResource\Pages\CreatePricingRule;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

describe('destinasi dan aktivitas', function () {
    it('creates a destination', function () {
        Livewire::test(CreateDestination::class)
            ->fillForm([
                'name' => 'Curug Cantik',
                'slug' => 'curug-cantik',
                'type' => 'destination',
                'status' => 'published',
                'sort_order' => 1,
                'latitude' => -6.9,
                'longitude' => 107.6,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $destination = Destination::sole();
        expect($destination->type)->toBe(DestinationType::Destination)
            ->and($destination->getTranslation('name', 'id'))->toBe('Curug Cantik');
    });

    it('creates an activity and validates coordinates', function () {
        Livewire::test(CreateDestination::class)
            ->fillForm(['name' => 'Arung Jeram', 'slug' => 'arung-jeram', 'type' => 'activity', 'status' => 'draft', 'sort_order' => 0, 'latitude' => 120])
            ->call('create')
            ->assertHasFormErrors(['latitude']);

        expect(Destination::count())->toBe(0);
    });

    it('lists, edits and deletes a destination', function () {
        $destination = Destination::factory()->create();

        Livewire::test(ListDestinations::class)->assertCanSeeTableRecords([$destination]);

        Livewire::test(EditDestination::class, ['record' => $destination->getRouteKey()])
            ->fillForm(['is_featured' => true, 'status' => 'draft'])
            ->call('save')
            ->assertHasNoFormErrors();
        expect($destination->fresh()->is_featured)->toBeTrue()
            ->and($destination->fresh()->status)->toBe(PublishStatus::Draft);

        Livewire::test(ListDestinations::class)->callTableAction('delete', $destination);
        expect(Destination::count())->toBe(0);
    });
});

describe('produk lokal', function () {
    it('creates a product and rejects an invalid WhatsApp number', function () {
        Livewire::test(CreateLocalProduct::class)
            ->fillForm(['name' => 'Keripik Singkong', 'whatsapp_number' => '12345', 'sort_order' => 0])
            ->call('create')
            ->assertHasFormErrors(['whatsapp_number']);

        Livewire::test(CreateLocalProduct::class)
            ->fillForm(['name' => 'Keripik Singkong', 'maker_name' => 'Ibu Siti', 'whatsapp_number' => '081234567890', 'sort_order' => 0, 'is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(LocalProduct::sole()->maker_name)->toBe('Ibu Siti');
    });
});

describe('fasilitas sewa', function () {
    it('creates a facility', function () {
        Livewire::test(CreateFacility::class)
            ->fillForm(['name' => 'Aula Desa', 'slug' => 'aula-desa', 'base_price' => 1500000, 'capacity' => 100, 'unit' => 'day', 'sort_order' => 0, 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Facility::sole()->base_price)->toBe(1500000);
    });

    it('hides delete for a facility that already has reservations', function () {
        $used = Reservation::factory()->create()->facility;
        $unused = Facility::factory()->create();

        Livewire::test(ListFacilities::class)
            ->assertTableActionHidden('delete', $used)
            ->assertTableActionVisible('delete', $unused);
    });

    it('creates a pricing rule for a facility', function () {
        $facility = Facility::factory()->create();

        Livewire::test(CreatePricingRule::class)
            ->fillForm(['facility_id' => $facility->id, 'name' => 'Akhir pekan', 'type' => 'weekend', 'price' => 900000, 'priority' => 1])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(PricingRule::sole()->price)->toBe(900000);
    });

    it('creates a blocked date range and rejects an end before the start', function () {
        $facility = Facility::factory()->create();

        Livewire::test(ManageBlockedDates::class)
            ->callAction('create', ['facility_id' => $facility->id, 'starts_on' => '2030-05-10', 'ends_on' => '2030-05-01'])
            ->assertHasActionErrors(['ends_on']);

        Livewire::test(ManageBlockedDates::class)
            ->callAction('create', ['facility_id' => $facility->id, 'starts_on' => '2030-05-10', 'ends_on' => '2030-05-12', 'reason' => 'Perawatan'])
            ->assertHasNoActionErrors();

        expect(BlockedDate::sole()->reason)->toBe('Perawatan');
    });
});

describe('event dan kegiatan', function () {
    it('creates an event with an automatic slug and stores Jakarta time as UTC', function () {
        Livewire::test(CreateEvent::class)
            ->fillForm(['title' => 'Festival Budaya', 'starts_at' => '2030-03-10 09:00:00', 'fee' => 0, 'status' => 'published'])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::sole();
        expect($event->slug)->toBe('festival-budaya')
            ->and($event->starts_at->toDateTimeString())->toBe('2030-03-10 02:00:00');
    });

    it('keeps automatic slugs unique', function () {
        Event::factory()->create(['slug' => 'festival-budaya']);

        Livewire::test(CreateEvent::class)
            ->fillForm(['title' => 'Festival Budaya', 'starts_at' => '2030-03-10 09:00:00', 'fee' => 0, 'status' => 'draft'])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Event::orderByDesc('id')->first()->slug)->toBe('festival-budaya-2');
    });

    it('requires the event to end after it starts', function () {
        Livewire::test(CreateEvent::class)
            ->fillForm(['title' => 'Salah', 'starts_at' => '2030-03-10 09:00:00', 'ends_at' => '2030-03-10 08:00:00', 'fee' => 0, 'status' => 'draft'])
            ->call('create')
            ->assertHasFormErrors(['ends_at']);
    });

    it('edits an event', function () {
        $event = Event::factory()->create(['status' => 'draft']);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm(['status' => 'cancelled', 'quota' => 25])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($event->fresh()->quota)->toBe(25)
            ->and($event->fresh()->status->value)->toBe('cancelled');
    });

    it('hides delete for an event that has registrations', function () {
        $used = EventRegistration::factory()->create()->event;
        $unused = Event::factory()->create();

        Livewire::test(ListEvents::class)
            ->assertTableActionHidden('delete', $used)
            ->assertTableActionVisible('delete', $unused);
    });

    it('cancels a registration from the event page', function () {
        $registration = EventRegistration::factory()->create();

        Livewire::test(RegistrationsRelationManager::class, ['ownerRecord' => $registration->event, 'pageClass' => EditEvent::class])
            ->callTableAction('cancel', $registration, ['reason' => 'Berhalangan'])
            ->assertHasNoTableActionErrors();

        expect($registration->fresh()->is_active)->toBeFalse();
    });
});

describe('cerita desa', function () {
    it('creates a story that appears on the landing page once published', function () {
        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Panen Padi Bersama',
                'slug' => 'panen-padi-bersama',
                'excerpt' => 'Warga memanen padi.',
                'category' => 'culture',
                'status' => 'published',
                'published_at' => '2030-03-10 09:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::sole();
        expect($article->getTranslation('title', 'id'))->toBe('Panen Padi Bersama')
            ->and($article->category)->toBe(ArticleCategory::Culture)
            ->and($article->published_at->toDateTimeString())->toBe('2030-03-10 02:00:00');
    });

    it('requires a unique slug and a category', function () {
        Article::factory()->create(['slug' => 'sudah-ada']);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Lain', 'slug' => 'sudah-ada', 'status' => 'draft'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique', 'category' => 'required']);
    });

    it('edits and deletes a story', function () {
        $article = Article::factory()->create();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['status' => 'draft', 'author_name' => 'Tim Desa'])
            ->call('save')
            ->assertHasNoFormErrors();
        expect($article->fresh()->author_name)->toBe('Tim Desa');

        Livewire::test(ListArticles::class)->callTableAction('delete', $article);
        expect(Article::count())->toBe(0);
    });
});

describe('tanya jawab', function () {
    it('creates, lists and deletes a question', function () {
        Livewire::test(CreateFaq::class)
            ->fillForm(['question' => 'Apakah perlu akun?', 'answer' => 'Tidak perlu.', 'sort_order' => 1, 'is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $faq = Faq::sole();
        expect($faq->getTranslation('answer', 'id'))->toBe('Tidak perlu.');

        Livewire::test(ListFaqs::class)
            ->assertCanSeeTableRecords([$faq])
            ->callTableAction('delete', $faq);
        expect(Faq::count())->toBe(0);
    });

    it('requires both a question and an answer', function () {
        Livewire::test(CreateFaq::class)
            ->fillForm(['sort_order' => 0])
            ->call('create')
            ->assertHasFormErrors(['question' => 'required', 'answer' => 'required']);
    });
});
