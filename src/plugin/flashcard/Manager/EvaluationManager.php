<?php

namespace Claroline\FlashcardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\FlashcardBundle\Entity\CardDrawnProgression;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;

class EvaluationManager
{
    private ObjectManager $om;
    private ResourceEvaluationManager $resourceEvalManager;
    private ResourceEvaluationRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceEvaluation $evaluation, FlashcardDeck $deck): ResourceEvaluation
    {
        $drawnCardCount = $deck->getDraw() ?? count($deck->getCards());

        $attemptCards = $this->om->getRepository(CardDrawnProgression::class)->findBy([
            'resourceEvaluation' => $evaluation
        ]);

        $seenCount = array_key_exists('nextCardIndex', $evaluation->getData()) ? $evaluation->getData()['nextCardIndex'] : 0;
        $seenCount++;

        $successCount = 0;
        foreach ($attemptCards as $attemptCard) {
            $successCount += $attemptCard->getSuccessCount() > 0;
        }

        $session = $evaluation->getData()['session'] ?? 1;
        $progression = ($successCount / $drawnCardCount) * 100;

        if ($session === 7 && $seenCount >= $drawnCardCount) {
            $status = AbstractEvaluation::STATUS_COMPLETED;
        } else {
            $status = AbstractEvaluation::STATUS_INCOMPLETE;
        }

        if ( $seenCount >= $drawnCardCount ) {
            if( $status != AbstractEvaluation::STATUS_COMPLETED ) {
                $session++;
            }
            $seenCount = 0;
        }

        if ($progression > 100) {
            $progression = 100;
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
            'data' => [
                'session' => $session,
                'nextCardIndex' => $seenCount,
            ],
        ];

        return $this->resourceEvalManager->updateAttempt($evaluation, $evaluationData);
    }

    public function updateCardDrawnProgression(Flashcard $card, User $user, $isSuccessful): array
    {
        $node = $card->getDeck()->getResourceNode();
        $attempt = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $cardsDrawnProgression = $this->om->getRepository(CardDrawnProgression::class)->findBy([
            'resourceEvaluation' => $attempt
        ]);

        foreach ($cardsDrawnProgression as $cardProgression) {
            if ($cardProgression->getFlashcard()->getId() === $card->getId()) {
                $cardDrawnProgression = $cardProgression;

                if(!$cardDrawnProgression->isAnswered()) {
                    $cardDrawnProgression->setSuccessCount(0);
                }

                if ('true' === $isSuccessful) {
                    $cardDrawnProgression->setSuccessCount($cardDrawnProgression->getSuccessCount() + 1);
                } else {
                    $cardDrawnProgression->setSuccessCount(0);
                }

                $this->om->persist($cardDrawnProgression);
                $this->om->flush();
            }
        }

        $this->update($attempt, $card->getDeck());

        return $cardsDrawnProgression;
    }
}
