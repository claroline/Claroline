<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Finder\Resource;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Doctrine\ORM\QueryBuilder;

class SectionCommentFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return SectionComment::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $userJoin = false;
        $sectionJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'resourceNode':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                        $sectionJoin = true;
                    }
                    $qb->join('s.resourceNode', 'r');
                    $qb->andWhere("r.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'type':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                        $sectionJoin = true;
                    }
                    $qb->andWhere("s.type = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'section':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                        $sectionJoin = true;
                    }
                    $qb->andWhere("s.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'meta.section.start':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                        $sectionJoin = true;
                    }
                    $qb->andWhere('s.start = :sectionStart');
                    $qb->setParameter('sectionStart', $filterValue);
                    break;
                case 'meta.section.end':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                        $sectionJoin = true;
                    }
                    $qb->andWhere('s.end = :sectionEnd');
                    $qb->setParameter('sectionEnd', $filterValue);
                    break;
                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'user.name':
                case 'meta.user.name':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(u.firstName), ' '), UPPER(u.lastName))",
                            ':name'
                        ),
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(u.lastName), ' '), UPPER(u.firstName))",
                            ':name'
                        )
                    ));
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;
                case 'user.firstName':
                case 'meta.user.firstName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.firstName LIKE :firstName');
                    $qb->setParameter('firstName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'user.lastName':
                case 'meta.user.lastName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }
                    $qb->andWhere('u.lastName LIKE :lastName');
                    $qb->setParameter('lastName', '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'user.firstName':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.firstName', $sortByDirection);
                    break;
                case 'user.lastName':
                case 'user.name':
                case 'meta.user.name':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->orderBy('u.lastName', $sortByDirection);
                    break;
                case 'meta.section.start':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                    }
                    $qb->orderBy('s.start', $sortByDirection);
                    break;
                case 'meta.section.end':
                    if (!$sectionJoin) {
                        $qb->join('obj.section', 's');
                    }
                    $qb->orderBy('s.end', $sortByDirection);
                    break;
                case 'meta.creationDate':
                    $qb->orderBy('obj.creationDate', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
