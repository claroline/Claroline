<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
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
        for ($i = count($cards) - 1; $i > 0; --$i) {
            $j = mt_rand(0, $i);
            $tmp = $cards[$i];
            $cards[$i] = $cards[$j];
            $cards[$j] = $tmp;
        }

        if ($limit && $limit < count($cards)) {
            $cards = $cards->slice(0, $limit);
        }

        return $cards;
    }

    public function keepCardInSession(int $session, CardDrawnProgression $cardDrawnProgression): bool
    {
        if (4 === $session) {
            return $cardDrawnProgression->getSuccessCount() <= 1;
        }

        if (5 === $session) {
            return 0 === $cardDrawnProgression->getSuccessCount();
        }

        if (6 === $session) {
            return $cardDrawnProgression->getSuccessCount() <= 2;
        }

        if (7 === $session) {
            return 0 === $cardDrawnProgression->getSuccessCount() || 3 === $cardDrawnProgression->getSuccessCount();
        }

        return true;
    }

    public function shouldResetAttempts(array $oldFlashcardDeck, array $newFlashcardDeck): bool
    {
        $resetAttempts = false;

        if ($oldFlashcardDeck['draw'] !== $newFlashcardDeck['draw']) {
            $resetAttempts = true;
        } elseif (count($oldFlashcardDeck['cards']) !== count($newFlashcardDeck['cards'])) {
            $resetAttempts = true;
        } else {
            $cardsIds = [];
            foreach ($newFlashcardDeck['cards'] as $card) {
                $cardsIds[] = $card['id'];
            }

            $cards = $oldFlashcardDeck['cards'];
            foreach ($cards as $card) {
                if (!in_array($card['id'], $cardsIds)) {
                    $resetAttempts = true;
                    break;
                }
            }
        }

        return $resetAttempts;
    }

    public function calculateSession(?ResourceEvaluation $attempt, FlashcardDeck $deck, User $user): ?ResourceEvaluation
    {
        $node = $deck->getResourceNode();
        $session = $attempt ? $attempt->getData()['session'] ?? 1 : 1;
        $cards = $attempt ? $attempt->getData()['cards'] ?? [] : [];
        $cardsSessionIds = $attempt ? $attempt->getData()['cardsSessionIds'] ?? [] : [];
        $cardsAnsweredIds = $attempt ? $attempt->getData()['cardsAnsweredIds'] ?? [] : [];

        $cards = array_map(function ($card) {
            return $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'id' => $card['id'],
            ]);
        }, $cards);
        $cardsAnswered = array_map(function ($id) {
            return $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'id' => $id,
            ]);
        }, $cardsAnsweredIds);

        if (0 === count($deck->getCards())) {
            return null;
        }

        if (!$attempt) {
            $attempt = $this->resourceEvalManager->createAttempt($node, $user, [
                'status' => AbstractEvaluation::STATUS_OPENED,
                'progression' => 0,
                'data' => [
                    'session' => $session,
                    'cards' => $cards,
                    'cardsSessionIds' => $cardsSessionIds,
                    'cardsAnsweredIds' => $cardsAnsweredIds,
                ],
            ]);
        }

        if (0 === count($deck->getCards())) {
            return $attempt;
        }

        $attemptCards = self::shuffleCardByAttempt($deck->getCards(), $attempt, $deck->getDraw());
        foreach ($attemptCards as $card) {
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

            if (!in_array($progression, $cards)) {
                $cards[] = $progression;
            }
        }

        $cardsSession = [];
        foreach ($cards as $cardProgression) {
            if ($this->keepCardInSession($session, $cardProgression)) {
                $cardsSession[] = $cardProgression;
            }
        }

        if (0 === count($cardsSession) && 1 === $session) {
            $attempt = $this->resourceEvalManager->updateAttempt($attempt, [
                'status' => AbstractEvaluation::STATUS_OPENED,
                'progression' => 0,
                'data' => [
                    'session' => 1,
                    'cards' => array_map(function ($card) {
                        return $this->cardDrawnProgressionSerializer->serialize($card);
                    }, $cards),
                    'cardsSessionIds' => array_map(function ($card) {
                        return $card->getId();
                    }, $cardsSession),
                    'cardsAnsweredIds' => array_map(function ($card) {
                        return $card->getId();
                    }, $cardsAnswered),
                ],
            ]);
        }

        foreach ($cardsAnswered as $cardProgression) {
            foreach ($cardsSession as $key => $cardSession) {
                if ($cardProgression->getId() === $cardSession->getId()) {
                    unset($cardsSession[$key]);
                    $cardsSession = array_values($cardsSession);
                }
            }
        }

        while (0 === count($cardsSession) && $session < 7) {
            ++$session;
            $cardsSession = [];
            $cardsAnswered = [];
            foreach ($cards as $cardProgression) {
                if ($this->keepCardInSession($session, $cardProgression)) {
                    $cardsSession[] = $cardProgression;
                }
            }
        }

        $successfulCards = 0;
        $totalCards = count($cardsSession) + count($cardsAnswered);
        foreach ($cardsAnswered as $cardProgression) {
            $successfulCards += $cardProgression->isSuccessful() ? 1 : 0;
        }
        if (0 === $totalCards) {
            $progression = 0;
        } else {
            $progression = (int) min(round($successfulCards / $totalCards * 100), 100);
        }

        if (7 === $session && 0 === count($cardsSession)) {
            if (100 === $progression) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_FAILED;
            }
        } else {
            $status = AbstractEvaluation::STATUS_INCOMPLETE;
        }

        $attempt = $this->resourceEvalManager->updateAttempt($attempt, [
            'status' => $status,
            'progression' => $progression,
            'data' => [
                'session' => $session,
                'cards' => array_map(function ($card) {
                    return $this->cardDrawnProgressionSerializer->serialize($card);
                }, $cards),
                'cardsSessionIds' => array_map(function ($card) {
                    return $card->getId();
                }, $cardsSession),
                'cardsAnsweredIds' => array_map(function ($card) {
                    return $card->getId();
                }, $cardsAnswered),
            ],
        ]);

        if (!$deck->getShowOverview() && !$deck->getShowEndPage()) {
            if (AbstractEvaluation::STATUS_COMPLETED === $status || AbstractEvaluation::STATUS_FAILED === $status) {
                $attempt = $this->calculateSession(null, $deck, $user);
            }
        }

        return $attempt;
    }

    public function answerSessionCard(ResourceEvaluation $attempt, CardDrawnProgression $cardProgression): ResourceEvaluation
    {
        $cards = $attempt->getData()['cards'] ?? [];
        $cardsSessionIds = $attempt->getData()['cardsSessionIds'] ?? [];
        $cardsAnsweredIds = $attempt->getData()['cardsAnsweredIds'] ?? [];

        $totalCards = count($cardsSessionIds) + count($cardsAnsweredIds);

        $cards = array_map(function ($card) {
            return $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'id' => $card['id'],
            ]);
        }, $cards);

        if (!in_array($cardProgression->getId(), $cardsAnsweredIds)) {
            $cardsAnsweredIds[] = $cardProgression->getId();
        }

        $cardsAnswered = array_map(function ($id) {
            return $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'id' => $id,
            ]);
        }, $cardsAnsweredIds);

        $successfulCards = 0;
        foreach ($cardsAnswered as $cardProgression) {
            $successfulCards += $cardProgression->isSuccessful() ? 1 : 0;
        }

        if (0 === $totalCards) {
            $progression = 0;
        } else {
            $progression = (int) min(round($successfulCards / $totalCards * 100), 100);
        }

        return $this->resourceEvalManager->updateAttempt($attempt, [
            'status' => $attempt->getStatus(),
            'progression' => $progression,
            'data' => [
                'session' => $attempt->getData()['session'],
                'cards' => array_map(function ($card) {
                    return $this->cardDrawnProgressionSerializer->serialize($card);
                }, $cards),
                'cardsSessionIds' => $cardsSessionIds,
                'cardsAnsweredIds' => $cardsAnsweredIds,
            ],
        ]);
    }
}
