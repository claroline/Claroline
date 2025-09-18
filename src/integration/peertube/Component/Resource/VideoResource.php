<?php

namespace Claroline\PeerTubeBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Component\Resource\UrlAdapterInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\EvaluationManager;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

final class VideoResource extends ResourceComponent implements UrlAdapterInterface, EvaluatedResourceInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Environment $templating,
        private readonly SerializerProvider $serializer,
        private readonly EvaluationManager $evaluationManager,
        private readonly PeerTubeManager $peerTubeManager
    ) {
    }

    public static function getName(): string
    {
        return 'peertube_video';
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    public static function getSubscribedEvents(): array
    {
        return array_merge([], parent::getSubscribedEvents(), [
            'resource.peertube_video.embed' => 'onEmbed',
        ]);
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
        $this->peerTubeManager->handleThumbnailForVideo($resource);
    }

    /** @param Video $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        $this->peerTubeManager->handleThumbnailForVideo($resource);

        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    public function onEmbed(EmbedResourceEvent $event): void
    {
        $event->setData(
            $this->templating->render('@ClarolinePeerTube/resource/embedded.html.twig', [
                'resource' => $event->getResource(),
            ])
        );
    }

    public function supportsUrl(string $url): int
    {
        $peertube = $this->peerTubeManager->extractUrlParts($url);

        if (!empty($peertube)) {
            $videoId = $this->peerTubeManager->getVideoUuid($peertube['server'], $peertube['shortUuid']);
            if (!empty($videoId)) {
                return UrlAdapterInterface::SUPPORTED;
            }
        }

        return UrlAdapterInterface::UNSUPPORTED;
    }

    public function fromUrl(string $url): ?array
    {
        $peertube = $this->peerTubeManager->extractUrlParts($url);
        if (!empty($peertube)) {
            $info = $this->peerTubeManager->getVideoInfo($peertube['server'], $peertube['shortUuid']);
            if (!empty($info)) {
                return [
                    'name' => $info['name'],
                    'poster' => !empty($info['thumbnailPath']) ? $peertube['server'].$info['thumbnailPath'] : null,
                ];
            }
        }

        return [];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
