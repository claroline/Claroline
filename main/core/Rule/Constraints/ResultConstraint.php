<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule\Constraints;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("claroline.rule.constraint")
 */
class ResultConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $resultComparisonTypes = Rule::getResultComparisonTypes();
        $nbOccurence = $this->getRule()->getOccurrence();
        $nbValidatedLogs = 0;
        $ruleResult = $this->getRule()->getResult();
        $ruleResultMax = $this->getRule()->getResultMax();

        foreach ($this->getAssociatedLogs() as $associatedLog) {
            $associatedLogDetails = $associatedLog->getDetails();

            $logResult = isset($associatedLogDetails['result']) ?
                $associatedLogDetails['result'] :
                (isset($associatedLogDetails['dropGrade']) ?
                    $associatedLogDetails['dropGrade'] :
                    null)
            ;
            $logResultMax = isset($associatedLogDetails['resultMax']) ?
                $associatedLogDetails['resultMax'] :
                null;

            $realResults = $this->computeRealResults(
                $ruleResult,
                $ruleResultMax,
                $logResult,
                $logResultMax
            );

            if (!is_null($realResults['log']) &&
                !is_null($realResults['rule']) &&
                version_compare(
                    $realResults['log'],
                    $realResults['rule'],
                    $resultComparisonTypes[$this->getRule()->getResultComparison()]
                )
            ) {
                ++$nbValidatedLogs;
            }
        }

        return $nbValidatedLogs >= $nbOccurence;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTo(Rule $rule)
    {
        return null !== $rule->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        return $queryBuilder;
    }

    private function computeRealResults(
        $ruleScore,
        $ruleScoreMax,
        $score,
        $scoreMax
    ) {
        $realResults = ['rule' => null, 'log' => null];

        if (!is_null($ruleScore) && !is_null($score)) {
            if (empty($ruleScoreMax) || empty($scoreMax)) {
                $realResults['rule'] = $ruleScore;
                $realResults['log'] = $score;
            } else {
                $realRuleScore = number_format(
                    round($ruleScore / $ruleScoreMax, 2),
                    2
                );
                $realScore = number_format(
                    round($score / $scoreMax, 2),
                    2
                );
                $realResults['rule'] = $realRuleScore;
                $realResults['log'] = $realScore;
            }
        }

        return $realResults;
    }
}
