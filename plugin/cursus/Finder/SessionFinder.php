<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\Cursus;
use Doctrine\ORM\QueryBuilder;

class SessionFinder extends AbstractFinder
{
    public function getClass()
    {
        return CourseSession::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.course', 'c');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organizations':
                    $qb->join('c.organizations', 'o');
                    $qb->andWhere("o.uuid IN (:{$filterName})");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'course':
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'courseTitle':
                    $qb->andWhere("UPPER(c.title) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'active':
                    $qb->andWhere('obj.startDate > :now');
                    $qb->andWhere('obj.endDate > :now');
                    $qb->setParameter('now', new \DateTime());
                    break;
                case 'not_ended':
                    $qb->andWhere('obj.endDate > :now');
                    $qb->setParameter('now', new \DateTime());
                    break;
                case 'terminated':
                    if ($filterValue) {
                        $qb->andWhere('obj.endDate < :endDate');
                    } else {
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->isNull('obj.endDate'),
                            $qb->expr()->gte('obj.endDate', ':endDate')
                        ));
                    }
                    $qb->setParameter('endDate', new \DateTime());
                    break;
                case 'user':
                    $qb->leftJoin('obj.sessionUsers', 'su');
                    $qb->leftJoin('su.user', 'u');
                    $qb->leftJoin('obj.sessionGroups', 'sg');
                    $qb->leftJoin('sg.group', 'g');
                    $qb->leftJoin('g.users', 'gu');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('u.uuid', ':userId'),
                        $qb->expr()->eq('gu.uuid', ':userId')
                    ));
                    $qb->setParameter('userId', $filterValue);
                    break;
                case 'cursusTitle':
                    $cursusQb = $this->om->createQueryBuilder();
                    $cursusQuery = $cursusQb
                        ->select('cursus')
                        ->from(Cursus::class, 'cursus')
                        ->andWhere('cursus.lft <= cc.lft')
                        ->andWhere('cursus.rgt >= cc.rgt')
                        ->andWhere('cursus.root = cc.root')
                        ->andWhere("UPPER(cursus.title) LIKE :{$filterName}");

                    $qb->join('c.cursus', 'cc');
                    $qb->andWhere($qb->expr()->exists($cursusQuery->getDQL()));
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
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
                case 'course':
                case 'courseTitle':
                    $qb->orderBy('c.title', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
