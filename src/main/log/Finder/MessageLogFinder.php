<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\LogBundle\Entity\MessageLog;
use Doctrine\ORM\QueryBuilder;

/**
 * @deprecated
 */
class MessageLogFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return MessageLog::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $doerJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'doer':
                    if (!$doerJoin) {
                        $qb->leftJoin('obj.doer', 'u');
                        $doerJoin = true;
                    }

                    $qb->andWhere('u.uuid = :doer');
                    $qb->setParameter('doer', $filterValue);
                    break;

                case 'organizations':
                    if (!$doerJoin) {
                        $qb->leftJoin('obj.doer', 'u');
                        $doerJoin = true;
                    }

                    $qb->leftJoin('u.userOrganizationReferences', 'ref');
                    $qb->leftJoin('ref.organization', 'o');

                    // get organizations from the group
                    $qb->leftJoin('u.groups', 'g');
                    $qb->leftJoin('g.organizations', 'go');

                    $qb->andWhere('(o.uuid IN (:organizations) OR go.uuid IN (:organizations))');
                    $qb->setParameter('organizations', is_array($filterValue) ? $filterValue : [$filterValue]);

                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
