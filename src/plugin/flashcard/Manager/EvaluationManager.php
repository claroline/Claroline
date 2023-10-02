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
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\UserProgression;

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

    public function update(ResourceNode $node, User $user, UserProgression $progression): ResourceEvaluation
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $deck = $progression->getFlashcard()->getDeck();

        $cardsSeen = $this->om->getRepository(UserProgression::class)->countCardsSeenByUserForDeck($user, $deck->getCards());

        $allCards = $deck->getCards()->count();

        $status = AbstractEvaluation::STATUS_OPENED;
        if ($allCards) {
            $progression = ($cardsSeen / $allCards) * 100;

            if ($progression >= 100) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateAttempt($evaluation, $evaluationData);
        } else {
            return $this->resourceEvalManager->createAttempt($node, $user, $evaluationData);
        }
    }

    public function updateUserProgression(Flashcard $card, User $user, $isSuccessful): UserProgression
    {
        $userProgression = $this->om->getRepository(UserProgression::class)->findOneBy([
            'user' => $user,
            'flashcard' => $card,
        ]);

        if (!$userProgression) {
            $userProgression = new UserProgression();
            $userProgression->setUser($user);
            $userProgression->setFlashcard($card);
        }

        $userProgression->setIsSuccessful('true' === $isSuccessful);
        $this->om->persist($userProgression);
        $this->om->flush();

        $this->update($card->getDeck()->getResourceNode(), $user, $userProgression);

        return $userProgression;
    }
}
