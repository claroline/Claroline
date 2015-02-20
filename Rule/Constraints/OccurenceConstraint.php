<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;

class OccurenceConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid               = false;
        $countedAssociatedLogs = count($this->getAssociatedLogs());

        if (0 < $countedAssociatedLogs && $countedAssociatedLogs >= $this->getRule()->getOccurrence()) {
            $isValid = true;
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTo(Rule $rule)
    {
        return null !== $rule->getOccurrence();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        return $queryBuilder;
    }
}
