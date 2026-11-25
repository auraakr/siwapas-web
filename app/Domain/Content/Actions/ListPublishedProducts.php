<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\LocalProduct;
use App\Domain\Content\Services\SiteContact;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ListPublishedProducts
{
    public function __construct(private SiteContact $siteContact) {}

    /**
     * Published UMKM products in the order the manager set, ready for the product cards.
     *
     * @return array<int, array{id: int, name: string, maker: ?string, description: ?string, image: ?string, images: array<int, string>, whatsapp_url: ?string}>
     */
    public function handle(?int $limit = null): array
    {
        return LocalProduct::query()
            ->with('media')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->when($limit, fn ($query) => $query->limit($limit))
            ->get()
            ->map(function (LocalProduct $product): array {
                $images = $product->getMedia('default')
                    ->map(fn (Media $media): string => $media->getUrl())
                    ->values()
                    ->all();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'maker' => $product->maker_name,
                    'description' => $product->description ?: null,
                    'image' => $images[0] ?? null,
                    'images' => $images,
                    'whatsapp_url' => $this->siteContact->whatsappUrl($product->whatsapp_number),
                ];
            })
            ->values()
            ->all();
    }
}
