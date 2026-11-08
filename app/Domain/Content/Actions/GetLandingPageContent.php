<?php

namespace App\Domain\Content\Actions;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Content\Services\SiteContact;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Spatie\MediaLibrary\HasMedia;

/**
 * Everything the public landing page shows, as plain arrays for Inertia.
 */
class GetLandingPageContent
{
    public function __construct(private SiteContact $siteContact) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'images' => [
                'hero' => $this->staticImage('hero'),
                'about' => $this->staticImage('about'),
            ],
            'video_url' => config('siwapas.landing.video_url') ?: null,
            'destinations' => $this->destinations(DestinationType::Destination, 4),
            'activities' => $this->destinations(DestinationType::Activity, 3),
            'facilities' => $this->facilities(),
            'products' => $this->products(),
            'events' => $this->events(),
            'articles' => $this->articles(),
            'faqs' => $this->faqs(),
        ];
    }

    /**
     * A photo dropped into public/images/landing (hero.jpg, about.jpg, ...), if present.
     */
    private function staticImage(string $name): ?string
    {
        foreach (['jpg', 'jpeg', 'webp', 'png'] as $extension) {
            if (is_file(public_path("images/landing/{$name}.{$extension}"))) {
                return asset("images/landing/{$name}.{$extension}");
            }
        }

        return null;
    }

    private function imageOf(HasMedia $model): ?string
    {
        return $model->getFirstMediaUrl('default') ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function destinations(DestinationType $type, int $limit): array
    {
        return Destination::query()
            ->with('media')
            ->where('type', $type)
            ->where('status', PublishStatus::Published)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (Destination $destination): array => [
                'id' => $destination->id,
                'name' => $destination->name,
                'summary' => $destination->summary,
                'address' => $destination->address,
                'image' => $this->imageOf($destination),
                'is_featured' => $destination->is_featured,
                'requires_reservation' => $destination->requires_reservation,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function facilities(): array
    {
        return Facility::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(fn (Facility $facility): array => [
                'slug' => $facility->slug,
                'name' => $facility->name,
                'capacity' => $facility->capacity,
                'base_price' => $facility->base_price,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return LocalProduct::query()
            ->with('media')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(function (LocalProduct $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'maker' => $product->maker_name,
                    'description' => $product->description,
                    'image' => $this->imageOf($product),
                    'whatsapp_url' => $this->siteContact->whatsappUrl($product->whatsapp_number),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function events(): array
    {
        return Event::query()
            ->where('status', EventStatus::Published)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn (Event $event): array => [
                'slug' => $event->slug,
                'title' => $event->title,
                'starts_at' => $event->starts_at->toIso8601String(),
                'location_name' => $event->location_name,
                'fee' => $event->fee,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function articles(): array
    {
        return Article::query()
            ->with('media')
            ->where('status', PublishStatus::Published)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'category' => $article->category->value,
                'author' => $article->author_name,
                'published_at' => $article->published_at?->toIso8601String(),
                'image' => $this->imageOf($article),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function faqs(): array
    {
        return Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(fn (Faq $faq): array => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ])
            ->values()
            ->all();
    }
}
