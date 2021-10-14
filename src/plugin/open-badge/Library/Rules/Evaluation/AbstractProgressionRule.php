<?php

namespace Claroline\OpenBadgeBundle\Library\Rules\Evaluation;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\AbstractRule;

abstract class AbstractProgressionRule extends AbstractRule
{
    /**
     * @param AbstractUserEvaluation[] $evaluations
     */
    protected function checkEvaluationProgressions(Rule $rule, array $evaluations): array
    {
        $ruleData = $rule->getData();
        $expectedProgression = $ruleData['value'];

        return array_map(function (ResourceUserEvaluation $evaluation) {
            return $evaluation->getUser();
        }, array_filter($evaluations, function (AbstractUserEvaluation $evaluation) use ($expectedProgression) {
            $progression = ($evaluation->getProgression() / $evaluation->getProgressionMax()) * 100;

            return $progression >= $expectedProgression;
        }));
    }
}
