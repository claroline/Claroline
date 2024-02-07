<?php

namespace Claroline\FlashcardBundle\Subscriber;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\EvaluationBundle\Repository\ResourceAttemptRepository;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Manager\FlashcardManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FlashcardDeckSubscriber implements EventSubscriberInterface
{
    private ObjectManager $om;
    private SerializerProvider $serializer;
    private TokenStorageInterface $tokenStorage;
    private FlashcardManager $flashcardManager;
    private EvaluationManager $evaluationManager;
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage,
        FlashcardManager $flashcardManager,
        EvaluationManager $evaluationManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->flashcardManager = $flashcardManager;
        $this->evaluationManager = $evaluationManager;
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.flashcard.load' => 'onLoad',
        ];
    }

    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var FlashcardDeck $flashcardDeck */
        $flashcardDeck = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $evaluation = null;
        $attempt = null;
        $flashcardProgression = null;

        if ($user instanceof User) {
            $evaluation = $this->evaluationManager->getResourceUserEvaluation($flashcardDeck->getResourceNode(), $user);
            $attempt = $this->resourceEvalRepo->findOneInProgress($flashcardDeck->getResourceNode(), $user);
            $attempt = $this->flashcardManager->calculateSession($attempt, $flashcardDeck, $user);
            $flashcardProgression = $attempt ? $attempt->getData()['cards'] ?? [] : [];
        }

        $event->setData([
            'attempt' => $this->serializer->serialize($attempt),
            'userEvaluation' => $evaluation ? $this->serializer->serialize($evaluation, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'flashcardDeck' => $this->serializer->serialize($flashcardDeck),
            'flashcardProgression' => $flashcardProgression,
        ]);

        $event->stopPropagation();
    }
}
