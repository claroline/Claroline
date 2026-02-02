<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\Sequence\Requirement;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;

class SequenceRequirementManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function fulfillRequirements(Sequence $sequence, User $user): bool
    {
        /** @var Requirement[] $requirements */
        $requirements = $sequence->getRequirements()->toArray();
        if (empty($requirements)) {
            return true;
        }

        foreach ($requirements as $requirement) {
            /** @var SequenceEvaluation $userEvaluation */
            $userEvaluation = $this->om->getRepository(SequenceEvaluation::class)->findOneBy([
                'sequence' => $requirement->getRequiredSequence(),
                'user' => $user,
            ]);

            if (empty($userEvaluation)) {
                return false;
            }

            if (
                ($requirement->getStatus() && $userEvaluation->getStatus() !== $requirement->getStatus())
                || ($requirement->getProgression() && $userEvaluation->getProgression() < $requirement->getProgression())
                || ($requirement->getMinScore() && $userEvaluation->getRelativeScore() < $requirement->getMinScore())
                || ($requirement->getMaxScore() && $userEvaluation->getRelativeScore() >= $requirement->getMaxScore())
            ) {
                return false;
            }
        }

        return true;
    }
}
