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
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;
use Claroline\TagBundle\Entity\TaggedObject;
use Doctrine\ORM\QueryBuilder;

class SessionFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Session::class;
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

                case 'workspace':
                    $qb->join('obj.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'location':
                    $qb->join('obj.location', 'l');
                    $qb->andWhere("l.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'status':
                    switch ($filterValue) {
                        case 'not_started':
                            $qb->andWhere('obj.startDate < :now');
                            break;
                        case 'in_progress':
                            $qb->andWhere('(obj.startDate <= :now AND obj.endDate >= :now)');
                            break;
                        case 'ended':
                            $qb->andWhere('obj.endDate < :now');
                            break;
                        case 'not_ended':
                            $qb->andWhere('obj.endDate >= :now');
                            break;
                    }

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
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\SessionUser', 'su', 'WITH', 'su.session = obj');
                    $qb->leftJoin('su.user', 'u');
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\SessionGroup', 'sg', 'WITH', 'sg.session = obj');
                    $qb->leftJoin('sg.group', 'g');
                    $qb->leftJoin('g.users', 'gu');
                    $qb->andWhere('su.confirmed = 1 AND su.validated = 1');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('u.uuid', ':userId'),
                        $qb->expr()->eq('gu.uuid', ':userId')
                    ));
                    $qb->setParameter('userId', $filterValue);
                    break;

                case 'userPending':
                    $qb->leftJoin('Claroline\CursusBundle\Entity\Registration\SessionUser', 'su', 'WITH', 'su.session = obj');
                    $qb->leftJoin('su.user', 'u');
                    $qb->andWhere('(su.confirmed = 0 AND su.validated = 0)');
                    $qb->andWhere('u.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;

                case 'courseTags':// it's not named tags because it will be handled by the default tag search otherwise
                    // I need to handle it manually because the tags are linked to the parent course (not the session)
                    $tags = is_string($filterValue) ? [$filterValue] : $filterValue;

                    // generate query for tags filter
                    $tagQueryBuilder = $this->om->createQueryBuilder();
                    $tagQueryBuilder
                        ->select('to.id')
                        ->from(TaggedObject::class, 'to')
                        ->innerJoin('to.tag', 't')
                        ->where('to.objectClass = :objectClass')
                        ->andWhere('to.objectId = c.uuid') // this makes the UUID required on tagged objects
                        ->andWhere('t.uuid IN (:tags)')
                        ->groupBy('to.objectId')
                        ->having('COUNT(to.id) = :expectedCount'); // this permits to make a AND between tags

                    // append sub query to the original one
                    $qb->andWhere($qb->expr()->exists($tagQueryBuilder->getDql()))
                        ->setParameter('objectClass', Course::class)
                        ->setParameter('tags', $tags)
                        ->setParameter('expectedCount', count($tags));

                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
