<?php

namespace Claroline\YouTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\EvaluationManager;
use Claroline\YouTubeBundle\Manager\YouTubeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VideoSubscriber extends ResourceComponent implements EvaluatedResourceInterface
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

    public static function getSubscribedEvents(): array
    {
        return array_merge([], parent::getSubscribedEvents(), [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Video::class) => 'onCrudCreate',
        ]);
    }

    /** @var Video $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return [
            'resource' => $this->serializer->serialize($resource),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
            'url' => $resource->getUrl(),
        ];
    }

    /** @var Video $resource */
    public function update(AbstractResource $resource, array $data): ?array
    {
        $this->youtubeManager->handleThumbnailForVideo($resource);

        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    public function onCrudCreate(CreateEvent $event): void
    {
        $video = $event->getObject();
        $this->youtubeManager->handleThumbnailForVideo($video);
    }

}
