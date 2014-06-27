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

class ResultConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid               = true;
        $resultComparisonTypes = Rule::getResultComparisonTypes();
        $nbOccurence           = $this->getRule()->getOccurrence();
        $nbValidatedLogs       = 0;

        foreach ($this->getAssociatedLogs() as $associatedLog) {
            $associatedLogDetails = $associatedLog->getDetails();

            if (isset($associatedLogDetails['result']) &&
                version_compare($associatedLogDetails['result'], $this->getRule()->getResult(), $resultComparisonTypes[$this->getRule()->getResultComparison()])) {
                $nbValidatedLogs++;
            }
        }

        return $nbValidatedLogs >= $nbOccurence;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTo(Rule $rule)
    {
        return (null !== $rule->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        return $queryBuilder;
    }
}
