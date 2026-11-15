<?php

namespace App\Domain\Content\Services;

/**
 * Pulls the video id out of the YouTube links people actually paste.
 */
class YoutubeUrl
{
    public static function idFrom(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $matched = preg_match(
            '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~',
            $url,
            $matches,
        );

        return $matched === 1 ? $matches[1] : null;
    }
}
