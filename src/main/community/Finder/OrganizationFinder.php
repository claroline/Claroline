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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\QueryBuilder;

class OrganizationFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Organization::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'location':
                    $qb->leftJoin('obj.locations', 'l');
                    $qb->andWhere('l.uuid IN (:locationIds)');
                    $qb->setParameter('locationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->leftJoin('obj.parent', 'p');
                        $qb->andWhere('p.uuid IN (:parentIds)');
                        $qb->setParameter('parentIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    }
                    break;
                case 'user':
                    $qb->leftJoin('obj.userOrganizationReferences', 'ur');
                    $qb->leftJoin('ur.user', 'u');
                    $qb->andWhere('u.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'group':
                    $qb->leftJoin('obj.groups', 'g');
                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspace':
                    $qb->leftJoin('obj.workspaces', 'w');
                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
