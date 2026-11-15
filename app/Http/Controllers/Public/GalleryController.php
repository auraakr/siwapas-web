<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Models\GalleryItem;
use App\Domain\Content\Services\YoutubeUrl;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __invoke(): Response
    {
        $items = GalleryItem::query()
            ->with('media')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(function (GalleryItem $item): ?array {
                $youtubeId = YoutubeUrl::idFrom($item->youtube_url);

                // An uploaded photo wins; a video without a cover falls back to YouTube's thumbnail.
                $image = $item->getFirstMediaUrl('default')
                    ?: ($youtubeId !== null ? "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg" : null);

                if ($image === null) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'title' => $item->title ?: null,
                    'category' => $item->category,
                    'image' => $image,
                    'youtube_id' => $youtubeId,
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('site/gallery', [
            'items' => $items->all(),
            'categories' => $items->pluck('category')->filter()->unique()->values()->all(),
        ]);
    }
}
