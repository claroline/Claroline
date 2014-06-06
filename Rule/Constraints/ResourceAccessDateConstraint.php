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

class ResourceAccessDateConstraint extends AbstractConstraint
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
        return ($rule->getAdditionalDatas() === 'resource_access_date');
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery(QueryBuilder $queryBuilder)
    {
        $resourceNode = $this->getRule()->getResource();
        $accessibleFrom = $resourceNode->getAccessibleFrom();
        $accessibleUntil = $resourceNode->getAccessibleUntil();
        $resultQueryBuilder = $queryBuilder;

        if (!is_null($accessibleFrom)) {
            $resultQueryBuilder = $resultQueryBuilder
                ->andWhere('l.dateLog >= :accessibleFrom')
                ->setParameter(
                    'accessibleFrom',
                    $accessibleFrom->format('Y-m-d H:i:s')
                );
        }
        if (!is_null($accessibleUntil)) {
            $resultQueryBuilder = $resultQueryBuilder
                ->andWhere('l.dateLog <= :accessibleUntil')
                ->setParameter(
                    'accessibleUntil',
                    $accessibleUntil->format('Y-m-d H:i:s')
                );
        }

        return $resultQueryBuilder;
    }
}
