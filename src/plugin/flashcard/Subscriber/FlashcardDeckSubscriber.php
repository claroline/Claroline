<?php

namespace Claroline\FlashcardBundle\Subscriber;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Serializer\UserProgressionSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FlashcardDeckSubscriber implements EventSubscriberInterface
{
    private SerializerProvider $serializer;
    private UserProgressionSerializer $userProgressionSerializer;
    private TokenStorageInterface $tokenStorage;
    private EvaluationManager $evaluationManager;

    public function __construct(
        SerializerProvider $serializer,
        UserProgressionSerializer $userProgressionSerializer,
        TokenStorageInterface $tokenStorage,
        EvaluationManager $evaluationManager
    ) {
        $this->serializer = $serializer;
        $this->userProgressionSerializer = $userProgressionSerializer;
        $this->tokenStorage = $tokenStorage;
        $this->evaluationManager = $evaluationManager;
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
        $progressionArray = [];

        foreach ($flashcardDeck->getCards() as $card) {
            $progression = $card->getProgressionByUser($user);
            if ($progression) {
                $progressionArray[] = $this->userProgressionSerializer->serialize($progression);
            }
        }

        $event->setData([
            'flashcardDeck' => $this->serializer->serialize($flashcardDeck),
            'flashcardDeckProgression' => $progressionArray ?? [],
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($flashcardDeck->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
        ]);
        $event->stopPropagation();
    }
}
