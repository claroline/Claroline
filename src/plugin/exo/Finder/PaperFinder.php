<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use UJM\ExoBundle\Entity\Attempt\Paper;

/**
 * Quiz papers finder.
 */
class PaperFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Paper::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'exercise':
                    $qb->join('obj.exercise', 'e');
                    $qb->andWhere('e.id = :exerciseId');
                    $qb->setParameter('exerciseId', $searches['exercise']);
                    break;
                case 'user':
                    $qb->join('obj.user', 'u');
                    $qb->andWhere('u.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;

                case 'finished':
                    $qb->andWhere("obj.interrupted != :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'finished':
                    $qb->orderBy('obj.interrupted', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
