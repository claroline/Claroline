<?php

namespace Claroline\YouTubeBundle\Component\Resource;

use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Component\Resource\UrlAdapterInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\YouTubeManager;

final class VideoResource extends ResourceComponent implements EvaluatedResourceInterface, UrlAdapterInterface
{
    public function __construct(
        private readonly YouTubeManager $youtubeManager
    ) {
    }

    public static function getName(): string
    {
        return 'youtube_video';
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    public function supportsUrl(string $url): int
    {
        if (str_starts_with($url, 'https://www.youtube.com')) {
            return UrlAdapterInterface::SUPPORTED;
        }

        return UrlAdapterInterface::UNSUPPORTED;
    }

    public function fromUrl(string $url): ?array
    {
        // https://www.googleapis.com/youtube/v3/videos?part=snippet&id=eUUviFnGnBE&key=API_KEY

        return [
            // snippet.title
            // snippet.description
            // snippet.thumbnails.default|snippet.thumbnails.medium|snippet.thumbnails.high|snippet.thumbnails.standard
            'poster' => $this->youtubeManager->getThumbnailUrl($url),
        ];
    }

    /** @param Video $resource */
    public function create(AbstractResource $resource, array $data): void
    {
        $this->youtubeManager->handleThumbnailForVideo($resource);
    }

    /** @param Video $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        $this->youtubeManager->handleThumbnailForVideo($resource);

        return [];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
