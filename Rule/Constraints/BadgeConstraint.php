<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;

class BadgeConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid = true;

        if (0 === count($this->getAssociatedLogs())) {
            $isValid = false;
        } else {
            foreach ($this->getAssociatedLogs() as $associatedLog) {
                $associatedLogDetails = $associatedLog->getDetails();

                if (isset($associatedLogDetails['badge'])) {
                    $isValid = $isValid && ($this->getRule()->getBadge()->getId() === $associatedLogDetails['badge']['id']);
                } else {
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function isApplicableTo(Rule $rule)
    {
        return (null !== $rule->getBadge());
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        return $queryBuilder;
    }
}
