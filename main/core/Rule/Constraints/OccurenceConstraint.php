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
class OccurenceConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid = false;
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
