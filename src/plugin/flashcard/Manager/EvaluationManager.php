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
use Claroline\FlashcardBundle\Entity\UserProgression;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var ResourceEvaluationRepository */
    private $resourceEvalRepo;

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

        $cardsSeen = $this->om->getRepository(UserProgression::class)->createQueryBuilder('up')
            ->select('count(up)')
            ->where('up.user = :user')
            ->andWhere('up.flashcard IN (:cards)')
            ->setParameter('user', $user->getId())
            ->setParameter('cards', $deck->getCards())
            ->getQuery()
            ->getSingleScalarResult();

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
}
