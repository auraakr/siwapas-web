<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Actions\ListPublishedProducts;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Destination;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DestinationController extends Controller
{
    /**
     * Destinations and UMKM products share one page; `?tab=umkm` opens the products tab directly.
     */
    public function index(Request $request, ListPublishedProducts $listPublishedProducts): Response
    {
        return Inertia::render('site/destinations/index', [
            'tab' => $request->query('tab') === 'umkm' ? 'umkm' : 'destinasi',
            'destinations' => $this->cards(DestinationType::Destination),
            'activities' => $this->cards(DestinationType::Activity),
            'products' => $listPublishedProducts->handle(),
        ]);
    }

    public function show(Destination $destination): Response
    {
        abort_unless($destination->status === PublishStatus::Published, 404);

        $hasPoint = filled($destination->latitude) && filled($destination->longitude);

        return Inertia::render('site/destinations/show', [
            'destination' => [
                'slug' => $destination->slug,
                'type' => $destination->type->value,
                'name' => $destination->name,
                'summary' => $destination->summary ?: null,
                'description' => $destination->description ?: null,
                'address' => $destination->address,
                'maps_url' => $hasPoint
                    ? "https://www.google.com/maps/search/?api=1&query={$destination->latitude},{$destination->longitude}"
                    : null,
                'video_url' => $destination->video_url,
                'requires_reservation' => $destination->requires_reservation,
                'images' => $destination->getMedia('default')
                    ->map(fn ($media): string => $media->getUrl())
                    ->values()
                    ->all(),
                'opening_hours' => collect($destination->opening_hours ?? [])
                    ->map(fn ($hours, $day): array => ['day' => (string) $day, 'hours' => (string) $hours])
                    ->values()
                    ->all(),
                'facilities' => array_values($destination->facilities ?? []),
                'rules' => array_values($destination->rules ?? []),
            ],
            'related' => $this->cards($destination->type, exclude: $destination->id, limit: 3),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cards(DestinationType $type, ?int $exclude = null, ?int $limit = null): array
    {
        return Destination::query()
            ->with('media')
            ->where('type', $type)
            ->where('status', PublishStatus::Published)
            ->when($exclude, fn ($query) => $query->whereKeyNot($exclude))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->when($limit, fn ($query) => $query->limit($limit))
            ->get()
            ->map(fn (Destination $destination): array => [
                'id' => $destination->id,
                'slug' => $destination->slug,
                'name' => $destination->name,
                'summary' => $destination->summary ?: null,
                'address' => $destination->address,
                'image' => $destination->getFirstMediaUrl('default') ?: null,
                'is_featured' => $destination->is_featured,
                'requires_reservation' => $destination->requires_reservation,
            ])
            ->values()
            ->all();
    }
}
