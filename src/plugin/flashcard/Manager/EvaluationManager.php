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

    public function update(ResourceEvaluation $evaluation, FlashcardDeck $deck ): ResourceEvaluation
    {
        $drawnCardCount = $deck->getDraw();

        if( !$drawnCardCount ){
            $drawnCardCount = count( $deck->getCards() );
        }

        $successCount = count( $this->om->getRepository(CardDrawnProgression::class)->findBy([
            'resourceEvaluation' => $evaluation
        ]));

        $progression = ($successCount / $drawnCardCount) * 100;

        if ($progression >= 100) {
            $status = AbstractEvaluation::STATUS_COMPLETED;
        } else {
            $status = AbstractEvaluation::STATUS_INCOMPLETE;
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
        ];

        return $this->resourceEvalManager->updateAttempt($evaluation, $evaluationData);
    }

    public function updateCardDrawnProgression(Flashcard $card, User $user, $isSuccessful): CardDrawnProgression
    {
        $node = $card->getDeck()->getResourceNode();
        $attempt = $this->resourceEvalRepo->findOneInProgress( $node, $user );

        // On vérifie qu'une tentative en cours existe, sinon on en crée une
        if ($attempt) {
            $cardDrawnProgression = $this->om->getRepository(CardDrawnProgression::class)->findOneBy([
                'resourceEvaluation' => $attempt,
                'flashcard' => $card
            ]);
        } else {
            $attempt = $this->resourceEvalManager->createAttempt($node, $user, [
                'status' => AbstractEvaluation::STATUS_OPENED,
                'progression' => 0,
            ]);
        }

        // Si la carte n'a jamais été tirée, on crée une progression et on la lie à cette tentative
        if( !isset( $cardDrawnProgression ) ){
            $cardDrawnProgression = new CardDrawnProgression();
            $cardDrawnProgression->setResourceEvaluation($attempt);
            $cardDrawnProgression->setFlashcard($card);
        }

        if ('true' === $isSuccessful) {
            $cardDrawnProgression->setSuccessCount($cardDrawnProgression->getSuccessCount() + 1);
        } else {
            $cardDrawnProgression->setSuccessCount(0);
        }

        $this->om->persist($cardDrawnProgression);
        $this->om->flush();

        // On met à jour la tentative et sa progression
        $this->update($attempt, $card->getDeck());

        return $cardDrawnProgression;
    }
}
