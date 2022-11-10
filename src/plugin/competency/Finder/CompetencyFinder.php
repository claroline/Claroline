<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use HeVinci\CompetencyBundle\Entity\Competency;

class CompetencyFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Competency::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $scaleJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->join('obj.parent', 'p');
                        $qb->andWhere('p.uuid IN (:parentIds)');
                        $qb->setParameter('parentIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    }
                    break;
                case 'scale':
                    if (!$scaleJoin) {
                        $qb->join('obj.scale', 's');
                        $scaleJoin = true;
                    }
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'scale.name':
                    if (!$scaleJoin) {
                        $qb->join('obj.scale', 's');
                        $scaleJoin = true;
                    }
                    $qb->andWhere('UPPER(s.name) LIKE :scaleName');
                    $qb->setParameter('scaleName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'resources':
                    $qb->join('obj.resources', 'r');
                    $qb->andWhere('r.uuid IN (:resourceIds)');
                    $qb->setParameter('resourceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'scale.name':
                    if (!$scaleJoin) {
                        $qb->join('obj.scale', 's');
                    }
                    $qb->orderBy('s.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
