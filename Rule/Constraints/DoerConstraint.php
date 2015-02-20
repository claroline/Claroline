<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;

class DoerConstraint extends AbstractConstraint
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
        $userTypes = Rule::getUserTypes();

        return (null !== $rule->getUser() && Rule::DOER_USER === $userTypes[$rule->getUserType()]);
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        return $queryBuilder
                ->andWhere('l.doer = :doer')
                ->setParameter('doer', $this->getRule()->getUser());
    }
}
