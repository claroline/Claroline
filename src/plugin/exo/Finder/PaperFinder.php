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
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
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

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $userJoin = false;
        if (!array_key_exists('user', $searches)) {
            // don't show evaluation of disabled/deleted users
            $qb->leftJoin('obj.user', 'u');
            $userJoin = true;

            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'exercise':
                    $qb->join('obj.exercise', 'e');
                    $qb->andWhere('e.id = :exerciseId');
                    $qb->setParameter('exerciseId', $searches['exercise']);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
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
