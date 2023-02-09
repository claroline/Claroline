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
        if ($expectedProgression > 100) {
            // progression is a percentage, it can not be over 100
            $expectedProgression = 100;
        }

        return array_map(function (ResourceUserEvaluation $evaluation) {
            return $evaluation->getUser();
        }, array_filter($evaluations, function (AbstractUserEvaluation $evaluation) use ($expectedProgression) {
            return $evaluation->getProgression() >= $expectedProgression;
        }));
    }
}
