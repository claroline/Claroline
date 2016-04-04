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
class RuleActiveDateConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        return 0 < count($this->getAssociatedLogs());
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function isApplicableTo(Rule $rule)
    {
        return !is_null($rule->getActiveFrom())
            || !is_null($rule->getActiveUntil());
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        $rule = $this->getRule();
        $activeFrom = $rule->getActiveFrom();
        $activeUntil = $rule->getActiveUntil();

        $resultQueryBuilder = $queryBuilder;

        if (!is_null($activeFrom)) {
            $resultQueryBuilder = $resultQueryBuilder
                ->andWhere('l.dateLog >= :activeFrom')
                ->setParameter(
                    'activeFrom',
                    $activeFrom->format('Y-m-d H:i:s')
                );
        }
        if (!is_null($activeUntil)) {
            $resultQueryBuilder = $resultQueryBuilder
                ->andWhere('l.dateLog <= :activeUntil')
                ->setParameter(
                    'activeUntil',
                    $activeUntil->format('Y-m-d H:i:s')
                );
        }

        return $resultQueryBuilder;
    }
}
