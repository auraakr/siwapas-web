<?php

namespace App\Http\Controllers\Public;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Page;
use App\Domain\Payment\Services\PaymentSettings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    /**
     * Planning a visit: the rentable facilities with prices, how booking works (the down payment
     * share comes from the payment settings) and the booking terms.
     */
    public function __invoke(PaymentSettings $paymentSettings): Response
    {
        $pages = Page::query()
            ->where('status', PublishStatus::Published)
            ->whereIn('key', ['visit_info', 'terms'])
            ->get()
            ->keyBy('key');

        return Inertia::render('site/visit', [
            'info' => $pages->has('visit_info') ? $this->pageData($pages->get('visit_info')) : null,
            'terms' => $pages->has('terms') ? $this->pageData($pages->get('terms')) : null,
            'dp_percentage' => $paymentSettings->downPaymentPercentage(),
            'facilities' => Facility::query()
                ->with('media')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (Facility $facility): array => [
                    'slug' => $facility->slug,
                    'name' => $facility->name,
                    'summary' => $facility->description ? Str::limit(trim(strip_tags($facility->description)), 140) : null,
                    'image' => $facility->getFirstMediaUrl('default') ?: null,
                    'base_price' => $facility->base_price,
                    'unit' => $facility->unit->value,
                    'capacity' => $facility->capacity,
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * A page with text, or null when it is empty, so the visitor never sees a blank section.
     *
     * @return array<string, string|null>|null
     */
    private function pageData(Page $page): ?array
    {
        if (trim(strip_tags((string) $page->body)) === '') {
            return null;
        }

        return [
            'title' => $page->title,
            'body' => $page->body,
            'seo_title' => $page->seo_title ?: null,
            'seo_description' => $page->seo_description ?: null,
        ];
    }
}
