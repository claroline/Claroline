<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class CourseSessionGroupRepository extends EntityRepository
{
    public function findOneSessionGroupBySessionAndGroup(
        CourseSession $session,
        Group $group,
        $groupType,
        $executeQuery = true
    ) {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
            WHERE csg.session = :session
            AND csg.group = :group
            AND csg.groupType = :groupType
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('group', $group);
        $query->setParameter('groupType', $groupType);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findSessionGroupsBySession(
        CourseSession $session,
        $executeQuery = true
    ) {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
            WHERE csg.session = :session
            ORDER BY csg.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionGroupsBySessionsAndGroup(
        array $sessions,
        Group $group,
        $groupType,
        $executeQuery = true
    ) {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
            WHERE csg.group = :group
            AND csg.groupType = :groupType
            AND csg.session IN (:sessions)
            ORDER BY csg.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sessions', $sessions);
        $query->setParameter('group', $group);
        $query->setParameter('groupType', $groupType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionGroupsByGroup(
        Group $group,
        $executeQuery = true
    ) {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
            WHERE csg.group = :group
            ORDER BY csg.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('group', $group);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredGroupsBySession(
        CourseSession $session,
        $groupType,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            WHERE NOT EXISTS (
                SELECT csg
                FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
                WHERE csg.session = :session
                AND csg.group = g
                AND csg.groupType = :groupType
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('groupType', $groupType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredGroupsBySession(
        CourseSession $session,
        $groupType,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            WHERE UPPER(g.name) LIKE :search
            AND NOT EXISTS (
                SELECT csg
                FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
                WHERE csg.session = :session
                AND csg.group = g
                AND csg.groupType = :groupType
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('groupType', $groupType);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }
}
