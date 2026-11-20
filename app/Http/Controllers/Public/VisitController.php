<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Page;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function __invoke(): Response
    {
        $pages = Page::query()
            ->where('status', PublishStatus::Published)
            ->whereIn('key', ['visit_info', 'terms'])
            ->get()
            ->keyBy('key');

        return Inertia::render('site/visit', [
            'info' => $pages->has('visit_info') ? $this->pageData($pages->get('visit_info')) : null,
            'terms' => $pages->has('terms') ? $this->pageData($pages->get('terms')) : null,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function pageData(Page $page): array
    {
        return [
            'title' => $page->title,
            'body' => $page->body ?: null,
            'seo_title' => $page->seo_title ?: null,
            'seo_description' => $page->seo_description ?: null,
        ];
    }
}
