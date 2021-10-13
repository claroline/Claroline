<?php

namespace Claroline\OpenBadgeBundle\Library\Rules\Evaluation;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\AbstractRule;

abstract class AbstractScoreRule extends AbstractRule
{
    /**
     * @param AbstractUserEvaluation[] $evaluations
     */
    protected function checkEvaluationScores(Rule $rule, array $evaluations): array
    {
        $ruleData = $rule->getData();
        $expectedScore = $ruleData['value'];

        return array_map(function (ResourceUserEvaluation $evaluation) {
            return $evaluation->getUser();
        }, array_filter($evaluations, function (AbstractUserEvaluation $evaluation) use ($expectedScore) {
            $scoreProgress = 0;
            if ($evaluation->getScoreMax()) {
                $scoreProgress = ($evaluation->getScore() ?? 0) / $evaluation->getScoreMax() * 100;
            }

            return $scoreProgress >= $expectedScore;
        }));
    }
}
