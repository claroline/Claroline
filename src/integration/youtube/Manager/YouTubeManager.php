<?php

namespace Claroline\YouTubeBundle\Manager;

class YouTubeManager
{
    public function checkUrl(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);

        if (empty($videoId)) {
            return 'The URL is not a correct YouTube URL.';
        }

        $headers = get_headers('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $videoId);
        if (!(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false)) {
            return 'This video does not exist.';
        }

        return null;
    }

    public function extractVideoId(string $url): ?string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        if (empty($query['v'])) {
            return null;
        }

        return $query['v'];
    }
}
