<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Doctrine\ORM\QueryBuilder;

class AssertionFinder extends AbstractFinder
{
    public function getClass()
    {
        return Assertion::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'badge':
                    $qb->join('obj.badge', 'b');
                    $qb->andWhere('b.uuid = :badge');
                    $qb->setParameter('badge', $filterValue);
                    break;
                case 'workspace':
                    $qb->join('obj.badge', 'b');
                    $qb->join('b.workspace', 'w');
                    $qb->andWhere('w.uuid = :workspace');
                    $qb->setParameter('workspace', $filterValue);
                    break;
                case 'user':
                case 'recipient':
                    $qb->join('obj.recipient', 'r');
                    $qb->andWhere('r.uuid = :user');
                    $qb->setParameter('user', $filterValue);
                    break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
