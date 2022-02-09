<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class ScoTrackingFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return 'Claroline\ScormBundle\Entity\ScoTracking';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.sco', 'sco');

        $userJoin = false;
        if (!array_key_exists('userDisabled', $searches) && !in_array('user', $searches) && !in_array('userEmail', $searches)) {
            // only return results for enabled users
            $qb->join('obj.user', 'u');
            $userJoin = true;

            $qb->andWhere('u.isEnabled = TRUE');
            $qb->andWhere('u.isRemoved = FALSE');
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'scorm':
                    $qb->join('sco.scorm', 'scorm');
                    $qb->andWhere('scorm.id = :scormId');
                    $qb->setParameter('scormId', $filterValue);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'userDisabled':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.isEnabled = :isEnabled');
                    $qb->andWhere('u.isRemoved = FALSE');
                    $qb->setParameter('isEnabled', !$filterValue);
                    break;

                case 'userEmail':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('UPPER(u.email) LIKE :email');
                    $qb->setParameter('email', '%'.strtoupper($filterValue).'%');
                    break;

                case 'sco':
                    $qb->andWhere("UPPER(sco.title) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'sco':
                    $qb->orderBy('sco.title', $sortByDirection);
                    break;
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.firstName', $sortByDirection);
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
                case 'totalTime':
                    $qb->orderBy('obj.totalTimeInt', $sortByDirection);
                    $qb->orderBy('obj.totalTimeString', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
