<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Serializer\CardDrawnProgressionSerializer;

class FlashcardManager
{
    private ObjectManager $om;
    private CardDrawnProgressionSerializer $cardDrawnProgressionSerializer;
    private ResourceEvaluationManager $resourceEvalManager;

    public function __construct(
        ObjectManager $om,
        CardDrawnProgressionSerializer $cardDrawnProgressionSerializer,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->cardDrawnProgressionSerializer = $cardDrawnProgressionSerializer;
        $this->resourceEvalManager = $resourceEvalManager;
    }

    public static function shuffleCardByAttempt($cards, ?ResourceEvaluation $attempt, ?int $limit)
    {
        mt_srand($attempt ? $attempt->getId() : 0);
        for ($i = count($cards) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $cards[$i];
            $cards[$i] = $cards[$j];
            $cards[$j] = $tmp;
        }

        if ($limit && $limit < count($cards)){
            $cards = $cards->slice(0, $limit);
        }

        return $cards;
    }

    public function getAttemptCardsProgression(FlashcardDeck $deck, $attempt, $user): array
    {
        $flashcardsProgression = [];

        if (!$attempt) {
            $attempt = $this->resourceEvalManager->createAttempt($deck->getResourceNode(), $user, [
                'status' => AbstractEvaluation::STATUS_OPENED,
                'progression' => 0,
                'data' => [
                    'session' => 1,
                    'nextCardIndex' => 0,
                ]
            ]);
        }

        $cards = self::shuffleCardByAttempt($deck->getCards(), $attempt, $deck->getDraw());
        foreach ($cards as $card) {
            $progression = $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'flashcard' => $card,
                'resourceEvaluation' => $attempt,
            ]);

            if (!$progression) {
                $progression = new CardDrawnProgression();
                $progression->setResourceEvaluation($attempt);
                $progression->setFlashcard($card);
                $progression->setSuccessCount(-1);
                $this->om->persist($progression);
                $this->om->flush();
            }

            $flashcardsProgression[] = $this->cardDrawnProgressionSerializer->serialize($progression);
        }

        return $flashcardsProgression;
    }

    public function shouldResetAttempts(FlashcardDeck $oldFlashcardDeck, array $newFlashcardDeck): bool
    {
        $resetAttempts = false;

        if ($oldFlashcardDeck->getDraw() !== $newFlashcardDeck['draw']) {
            $resetAttempts = true;
        } elseif (count($oldFlashcardDeck->getCards()) !== count($newFlashcardDeck['cards'])) {
            $resetAttempts = true;
        }

        $cardsIds = [];
        foreach ($newFlashcardDeck['cards'] as $card) {
            $cardsIds[] = $card['id'];
        }

        if (!$resetAttempts) {
            $cards = $oldFlashcardDeck->getCards();
            foreach ($cards as $card) {
                if (!in_array($card->getUuid(), $cardsIds)) {
                    $resetAttempts = true;
                    break;
                }
            }
        }

        return $resetAttempts;
    }
}
