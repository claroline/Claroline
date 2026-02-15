<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Repository\UserEvaluation\ResourceAttemptRepository;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;
use Claroline\FlashcardBundle\Entity\Flashcard;

class EvaluationManager
{
    private ObjectManager $om;
    private ResourceEvaluationManager $resourceEvalManager;
    private FlashcardManager $flashcardManager;
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        FlashcardManager $flashcardManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->flashcardManager = $flashcardManager;
        $this->resourceEvalRepo = $this->om->getRepository(ResourceAttempt::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceAttempt $attempt, $attemptCards): ResourceAttempt
    {
        $drawnCardCount = count($attemptCards);

        $seenCount = array_key_exists('nextCardIndex', $attempt->getData()) ? $attempt->getData()['nextCardIndex'] : 0;
        ++$seenCount;

        $successCount = 0;
        foreach ($attemptCards as $attemptCard) {
            $successCount += $attemptCard->getSuccessCount() > 0;
        }

        $session = $attempt->getData()['session'] ?? 1;

        if ($drawnCardCount > 0) {
            $progression = ($successCount / $drawnCardCount) * 100;
        } else {
            $progression = 100;
        }

        if (7 === $session && $seenCount >= $drawnCardCount && $successCount >= $drawnCardCount) {
            $status = EvaluationStatus::COMPLETED;
        } elseif (7 === $session && $seenCount >= $drawnCardCount) {
            $status = EvaluationStatus::FAILED;
        } else {
            $status = EvaluationStatus::INCOMPLETE;
        }

        if ($seenCount >= $drawnCardCount) {
            if (EvaluationStatus::COMPLETED != $status && EvaluationStatus::FAILED != $status) {
                ++$session;
            }
            $seenCount = 0;
        }

        if ($progression > 100) {
            $progression = 100;
        }

        $cardsArray = $attemptCards;
        foreach ($cardsArray as $cardProgression) {
            if (!$this->flashcardManager->keepCardInSession($cardProgression, $session)) {
                unset($cardsArray[array_search($cardProgression, $cardsArray)]);
            }
        }

        if (0 === count($cardsArray)) {
            ++$session;
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
            'data' => [
                'session' => $session,
                'nextCardId' => $seenCount,
            ],
        ];

        return $this->resourceEvalManager->updateAttempt($attempt, $evaluationData);
    }

    public function updateCardDrawnProgression(Flashcard $card, User $user, $isSuccessful): void
    {
        $node = $card->getDeck()->getResourceNode();
        $attempt = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $cardsDrawnProgression = $this->om->getRepository(CardDrawnProgression::class)->findBy([
            'resourceEvaluation' => $attempt,
        ]);

        foreach ($cardsDrawnProgression as $cardProgression) {
            if ($cardProgression->getFlashcard()->getId() === $card->getId()) {
                $cardDrawnProgression = $cardProgression;

                if (!$cardDrawnProgression->isAnswered()) {
                    $cardDrawnProgression->setSuccessCount(0);
                }

                if ('true' === $isSuccessful) {
                    $cardDrawnProgression->setSuccessCount($cardDrawnProgression->getSuccessCount() + 1);
                } else {
                    $cardDrawnProgression->setSuccessCount(0);
                }

                $this->om->persist($cardDrawnProgression);
                $this->om->flush();
                $this->flashcardManager->answerSessionCard($attempt, $cardDrawnProgression);
            }
        }
    }
}
