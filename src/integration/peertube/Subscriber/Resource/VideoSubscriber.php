<?php

namespace Claroline\PeerTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\EvaluationManager;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class VideoSubscriber extends ResourceComponent
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

    public static function getSubscribedEvents(): array
    {
        return array_merge([], parent::getSubscribedEvents(), [
            'resource.peertube_video.embed' => 'onEmbed',
            Crud::getEventName('create', 'post', Video::class) => 'onCrudCreate',
            Crud::getEventName('update', 'post', Video::class) => 'postUpdate',
        ]);
    }

    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'video' => $this->serializer->serialize($resource),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
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

    public function onCrudCreate(CreateEvent $event): void
    {
        $video = $event->getObject();
        $this->peerTubeManager->handleThumbnailForVideo($video);
    }

    public function postUpdate(UpdateEvent $event): void
    {
        $video = $event->getObject();
        $this->peerTubeManager->handleThumbnailForVideo($video);
    }
}
