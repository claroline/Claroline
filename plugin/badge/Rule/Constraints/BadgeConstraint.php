<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Claroline\CoreBundle\Rule\Constraints\AbstractConstraint;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;
use Icap\BadgeBundle\Entity\BadgeRule;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("claroline.rule.constraint")
 */
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
        return $rule instanceof BadgeRule && null !== $rule->getBadge();
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
