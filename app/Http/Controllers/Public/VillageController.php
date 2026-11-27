<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\TeamMember;
use App\Domain\Content\Services\MapLinks;
use App\Domain\Content\Services\SiteSettings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class VillageController extends Controller
{
    /**
     * The profile pages, in the order they appear. Only published pages with text are shown;
     * nothing is filled in for the managers.
     */
    private const KEYS = ['profile', 'history', 'vision', 'mission', 'management', 'potential'];

    public function __invoke(SiteSettings $settings, MapLinks $mapLinks): Response
    {
        $pages = Page::query()
            ->with('media')
            ->where('status', PublishStatus::Published)
            ->whereIn('key', self::KEYS)
            ->get()
            ->keyBy('key');

        $contact = $settings->contact();

        return Inertia::render('site/village', [
            'about' => $this->section($pages, 'profile'),
            'history' => $this->section($pages, 'history'),
            'vision' => $this->plainText($pages->get('vision')?->body),
            'mission' => $this->listItems($pages->get('mission')?->body),
            'management' => $this->section($pages, 'management'),
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
            'potential' => $this->section($pages, 'potential'),
            'location' => $contact['address'] !== null || $contact['maps_url'] !== null
                ? [
                    'address' => $contact['address'],
                    'embed_url' => $mapLinks->embedUrl($contact['maps_url'], $contact['address']),
                    'directions_url' => $mapLinks->directionsUrl($contact['maps_url'], $contact['address']),
                ]
                : null,
            'seo' => [
                'title' => $pages->get('profile')?->seo_title ?: null,
                'description' => $pages->get('profile')?->seo_description ?: null,
            ],
        ]);
    }

    /**
     * A page with text, or null when it is missing, a draft, or empty.
     *
     * @param  Collection<string, Page>  $pages
     * @return array{body: string, image: string|null}|null
     */
    private function section(Collection $pages, string $key): ?array
    {
        $page = $pages->get($key);

        if ($page === null || $this->plainText($page->body) === null) {
            return null;
        }

        return [
            'body' => (string) $page->body,
            'image' => $page->getFirstMediaUrl('default') ?: null,
        ];
    }

    /**
     * Rich text reduced to plain text (line breaks kept), or null when nothing is left.
     */
    private function plainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = preg_replace('#<br\s*/?>|</(p|li|h[1-6]|blockquote)>#i', "\n", $html);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t\u{00A0}]+/u", ' ', $text);
        $text = trim((string) preg_replace("/\n\s*\n+/", "\n", (string) $text));

        return $text === '' ? null : $text;
    }

    /**
     * Mission points: the items of the list the managers wrote, or one point per line/paragraph when
     * there is no list. Leading "1." or "-" markers are dropped since the page numbers the points.
     *
     * @return list<string>
     */
    private function listItems(?string $html): array
    {
        if ($html === null) {
            return [];
        }

        $chunks = preg_match_all('#<li[^>]*>(.*?)</li>#is', $html, $matches) > 0
            ? $matches[1]
            : explode("\n", (string) $this->plainText($html));

        $items = [];

        foreach ($chunks as $chunk) {
            $text = $this->plainText($chunk);
            $text = $text === null ? '' : trim((string) preg_replace('/^\s*(\d+[.)]|[-•*])\s*/u', '', str_replace("\n", ' ', $text)));

            if ($text !== '') {
                $items[] = $text;
            }
        }

        return $items;
    }
}
