<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder\Registration;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Doctrine\ORM\QueryBuilder;

class EventUserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return EventUser::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $userJoin = false;
        if (!array_key_exists('user', $searches)) {
            $qb->join('obj.user', 'u');
            $userJoin = true;

            // automatically excludes results for disabled/deleted users
            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'event':
                    $qb->join('obj.event', 'se');
                    $qb->andWhere("se.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'organizations':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    // get user organizations
                    $qb->leftJoin('u.userOrganizationReferences', 'ref');
                    $qb->leftJoin('ref.organization', 'o');

                    // get organizations from user groups
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
