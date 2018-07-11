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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Quiz papers finder.
 *
 * @DI\Service("claroline.api.finder.scorm.sco.tracking")
 * @DI\Tag("claroline.finder")
 */
class ScoTrackingFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\ScormBundle\Entity\ScoTracking';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.sco', 'sco');
        $qb->join('sco.scorm', 'scorm');
        $qb->andWhere('scorm.id = :scormId');
        $qb->setParameter('scormId', $searches['scorm']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'scorm':
                    break;
                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere("
                        UPPER(u.firstName) LIKE :name
                        OR UPPER(u.lastName) LIKE :name
                        OR UPPER(u.username) LIKE :name
                        OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :name
                        OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :name
                    ");
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;
                case 'sco':
                    $qb->andWhere("UPPER(sco.title) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
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
                    $qb->join('obj.user', 'uu');
                    $qb->orderBy('uu.firstName', $sortByDirection);
                    $qb->orderBy('uu.lastName', $sortByDirection);
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
