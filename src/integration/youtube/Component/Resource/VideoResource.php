<?php

namespace Claroline\YouTubeBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Component\Resource\UrlAdapterInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\EvaluationManager;
use Claroline\YouTubeBundle\Manager\YouTubeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class VideoResource extends ResourceComponent implements EvaluatedResourceInterface, UrlAdapterInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly EvaluationManager $evaluationManager,
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
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return [
            'resource' => $this->serializer->serialize($resource),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
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

        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
