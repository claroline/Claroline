<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\QueryBuilder;

class GroupFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Group::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $organizationJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organization':
                case 'organizations':
                    if (!$organizationJoin) {
                        $qb->leftJoin('obj.organizations', 'o');
                        $organizationJoin = true;
                    }

                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'administrated':
                    if (!$organizationJoin) {
                        $qb->leftJoin('obj.organizations', 'o');
                        $organizationJoin = true;
                    }
                    // TODO
                    break;
                case 'user':
                case 'users':
                    $qb->leftJoin('obj.users', 'gu');
                    $qb->andWhere('gu.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'location':
                case 'locations':
                    $qb->leftJoin('obj.locations', 'l');
                    $qb->andWhere('l.uuid IN (:locationIds)');
                    $qb->setParameter('locationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'role':
                case 'roles':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere('r.uuid IN (:roleIds)');
                    $qb->setParameter('roleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspace':
                    $qb->leftJoin('obj.roles', 'wsgroles');
                    $qb->leftJoin('wsgroles.workspace', 'rws');
                    $qb->andWhere('rws.uuid = (:workspaceId)');
                    $qb->setParameter('workspaceId', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    protected function getExtraFieldMapping(): array
    {
        return [
            'meta.description' => 'description',
        ];
    }
}
