<?php

namespace Claroline\VideoPlayerBundle\Listener\File\Type;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\VideoPlayerBundle\Manager\EvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VideoListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SerializerProvider */
    private $serializer;
    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
    }

    public function onLoad(LoadFileEvent $event)
    {
        /** @var File $resource */
        $video = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $event->setData(array_merge([
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($video->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
        ], $event->getData()));
    }
}
