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
    public static function getClass(): string
    {
        return Assertion::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = [])
    {
        $userJoin = false;

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
                    if (!$userJoin) {
                        $qb->join('obj.recipient', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.uuid = :user');
                    $qb->setParameter('user', $filterValue);
                    break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.recipient', 'u');
                    }
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
