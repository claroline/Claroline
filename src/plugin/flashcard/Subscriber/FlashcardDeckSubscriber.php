<?php

namespace Claroline\FlashcardBundle\Subscriber;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Manager\FlashcardManager;
use Claroline\FlashcardBundle\Serializer\CardDrawnProgressionSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FlashcardDeckSubscriber implements EventSubscriberInterface
{
    private SerializerProvider $serializer;
    private CardDrawnProgressionSerializer $cardDrawnProgressionSerializer;
    private TokenStorageInterface $tokenStorage;
    private ObjectManager $om;
    private EvaluationManager $evaluationManager;
    private FlashcardManager $flashcardManager;
    private ResourceEvaluationRepository $resourceEvalRepo;

    public function __construct(
        SerializerProvider             $serializer,
        CardDrawnProgressionSerializer $cardDrawnProgressionSerializer,
        TokenStorageInterface          $tokenStorage,
        EvaluationManager              $evaluationManager,
        ObjectManager                  $om,
        FlashcardManager               $flashcardManager
    )
    {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->flashcardManager = $flashcardManager;
        $this->evaluationManager = $evaluationManager;
        $this->cardDrawnProgressionSerializer = $cardDrawnProgressionSerializer;
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

        $evaluation = $this->evaluationManager->getResourceUserEvaluation($flashcardDeck->getResourceNode(), $user);
        $lastAttempt = $this->resourceEvalRepo->findLast($flashcardDeck->getResourceNode(), $user);

        $flashcardsProgression = [];
        foreach ($flashcardDeck->getCards() as $card) {
            $progression = $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'flashcard' => $card,
                'resourceEvaluation' => $lastAttempt,
            ]);
            if ($progression) {
                $flashcardsProgression[] = $this->cardDrawnProgressionSerializer->serialize($progression);
            }
        }

        $event->setData([
            'attempt' => $this->serializer->serialize($lastAttempt),
            'userEvaluation' => $this->serializer->serialize($evaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
            'flashcardDraw' => $this->flashcardManager->getCardDraw($flashcardDeck, $lastAttempt),
            'flashcardDeck' => $this->serializer->serialize($flashcardDeck),
            'flashcardDeckProgression' => $flashcardsProgression ?? [],
        ]);
        $event->stopPropagation();
    }
}
