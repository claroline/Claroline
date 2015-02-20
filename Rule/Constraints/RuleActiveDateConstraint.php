<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;

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
