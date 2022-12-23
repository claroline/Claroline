<?php

namespace Claroline\PeerTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\EvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class VideoSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Environment */
    private $templating;
    /** @var SerializerProvider */
    private $serializer;
    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.peertube_video.load' => 'onLoad',
            'resource.peertube_video.embed' => 'onEmbed',
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
}
