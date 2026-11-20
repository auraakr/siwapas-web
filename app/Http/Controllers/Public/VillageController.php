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
    /**
     * Page key => anchor on the village profile, in the order they appear.
     *
     * @var array<string, string>
     */
    private const SECTIONS = [
        'profile' => 'identitas',
        'history' => 'sejarah',
        'vision' => 'visi-misi',
        'management' => 'pengelola',
        'potential' => 'potensi',
    ];

    public function __invoke(): Response
    {
        $pages = Page::query()
            ->with('media')
            ->where('status', PublishStatus::Published)
            ->whereIn('key', array_keys(self::SECTIONS))
            ->get()
            ->keyBy('key');

        $sections = collect(self::SECTIONS)
            ->map(function (string $anchor, string $key) use ($pages): ?array {
                $page = $pages->get($key);

                if ($page === null) {
                    return null;
                }

                return [
                    'key' => $key,
                    'anchor' => $anchor,
                    'title' => $page->title,
                    'body' => $page->body ?: null,
                    'image' => $page->getFirstMediaUrl('default') ?: null,
                    'seo_title' => $page->seo_title ?: null,
                    'seo_description' => $page->seo_description ?: null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return Inertia::render('site/village', [
            'sections' => $sections,
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
}
