<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Rule\Entity\Rule;
use Doctrine\ORM\QueryBuilder;

class ActionConstraint extends AbstractConstraint
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
        return (null !== $rule->getAction());
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        $action = $this->getRule()->getAction();
        $foundType = preg_match('/\[\[(.*)\]\]/', $action, $matches);

        if ($foundType) {
            $type = $matches[1];
            $parts = explode(']]', $action);
            $action = ($foundType) ? $parts[1]: $action;

            return $queryBuilder
                ->join('l.resourceType', 'rt')
                ->andWhere('l.action = :action')
                ->andWhere('rt.name = :type')
                ->setParameter('action', $action)
                ->setParameter('type', $type);
        } else {
            return $queryBuilder
                ->andWhere('l.action = :action')
                ->setParameter('action', $action);
        }
    }
}
