<?php

namespace Claroline\PeerTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\EvaluationManager;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class VideoSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private Environment $templating;
    private SerializerProvider $serializer;
    private EvaluationManager $evaluationManager;
    private PeerTubeManager $peerTubeManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager,
        PeerTubeManager $peerTubeManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
        $this->peerTubeManager = $peerTubeManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.peertube_video.load' => 'onLoad',
            'resource.peertube_video.embed' => 'onEmbed',
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
        ]);
        $event->stopPropagation();
    }

    public function onEmbed(EmbedResourceEvent $event): void
    {
        $event->setData(
            $this->templating->render('@ClarolinePeerTube/resource/embedded.html.twig', [
                'resource' => $event->getResource(),
            ])
        );
    }

    public function onCreate(CreateEvent $event): void
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
