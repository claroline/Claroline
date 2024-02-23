<?php

namespace Claroline\YouTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\EvaluationManager;
use Claroline\YouTubeBundle\Manager\YouTubeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VideoSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private SerializerProvider $serializer;
    private EvaluationManager $evaluationManager;
    private YouTubeManager $youtubeManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager,
        YouTubeManager $youtubeManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
        $this->youtubeManager = $youtubeManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.youtube_video.load' => 'onLoad',
            Crud::getEventName('create', 'post', Video::class) => 'onCreate',
            Crud::getEventName('update', 'post', Video::class) => 'postUpdate',
        ];
    }

    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var Video $video */
        $video = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $event->setData([
            'video' => $this->serializer->serialize($video),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($video->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
            'url' => $video->getUrl(),
        ]);
        $event->stopPropagation();
    }

    public function onCreate(CreateEvent $event): void
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
