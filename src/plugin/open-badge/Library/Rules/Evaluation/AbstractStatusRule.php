<?php

namespace Claroline\OpenBadgeBundle\Library\Rules\Evaluation;

use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\AbstractRule;

abstract class AbstractStatusRule extends AbstractRule
{
    /**
     * @param AbstractUserEvaluation[] $evaluations
     */
    protected function checkEvaluationStatuses(Rule $rule, array $evaluations): array
    {
        $ruleData = $rule->getData();
        $expectedStatus = $ruleData['value'];

        return array_map(function (AbstractUserEvaluation $evaluation) {
            return $evaluation->getUser();
        }, array_filter($evaluations, function (AbstractUserEvaluation $evaluation) use ($expectedStatus) {
            if (EvaluationStatus::PRIORITY[$expectedStatus] <= EvaluationStatus::PRIORITY[$evaluation->getStatus()]) {
                return true;
            }

            return false;
        }));
    }
}
