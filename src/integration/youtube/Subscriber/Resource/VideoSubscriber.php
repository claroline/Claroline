<?php

namespace Claroline\YouTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
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
            Crud::getEventName('create', 'post', Video::class) => 'onCrudCreate',
            Crud::getEventName('update', 'post', Video::class) => 'postUpdate',
        ]);
    }

    /** @var Video $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'video' => $this->serializer->serialize($resource),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
            'url' => $resource->getUrl(),
        ];
    }

    public function onCrudCreate(CreateEvent $event): void
    {
        $video = $event->getObject();
        $this->youtubeManager->handleThumbnailForVideo($video);
    }

    public function postUpdate(UpdateEvent $event): void
    {
        $video = $event->getObject();
        $this->youtubeManager->handleThumbnailForVideo($video);
    }
}
