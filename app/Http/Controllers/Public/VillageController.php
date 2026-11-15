<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\TeamMember;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class VillageController extends Controller
{
    public function __invoke(): Response
    {
        $pages = Page::query()
            ->where('status', PublishStatus::Published)
            ->whereIn('key', ['profile', 'vision'])
            ->get()
            ->keyBy('key');

        return Inertia::render('site/village', [
            'story' => $pages->has('profile') ? $this->pageData($pages->get('profile')) : null,
            'vision' => $pages->has('vision') ? $this->pageData($pages->get('vision')) : null,
            'team' => TeamMember::query()
                ->with('media')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (TeamMember $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role,
                    'bio' => $member->bio ?: null,
                    'image' => $member->getFirstMediaUrl('default') ?: null,
                ])
                ->values()
                ->all(),
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
