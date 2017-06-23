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

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventSet;
use Doctrine\ORM\EntityRepository;

class SessionEventUserRepository extends EntityRepository
{
    public function findSessionEventUsersBySessionEvent(SessionEvent $sessionEvent)
    {
        $dql = "
            SELECT seu
            FROM Claroline\CursusBundle\Entity\SessionEventUser seu
            JOIN seu.sessionEvent se
            JOIN seu.user u
            WHERE se = :sessionEvent
            ORDER BY seu.registrationStatus DESC, u.lastName, u.firstName
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sessionEvent', $sessionEvent);

        return $query->getResult();
    }

    public function findUnregisteredUsersFromListBySessionEvent(SessionEvent $sessionEvent, array $users)
    {
        $dql = "
            SELECT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isEnabled = true
            AND u IN (:users)
            AND NOT EXISTS (
                SELECT seu
                FROM Claroline\CursusBundle\Entity\SessionEventUser seu
                JOIN seu.sessionEvent se
                JOIN seu.user uu
                WHERE se = :sessionEvent
                AND uu = u
            )
            ORDER BY u.lastName, u.firstName
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sessionEvent', $sessionEvent);
        $query->setParameter('users', $users);

        return $query->getResult();
    }

    public function findSessionEventUsersFromListBySessionEventAndStatus(SessionEvent $sessionEvent, array $users, $status)
    {
        $dql = "
            SELECT seu
            FROM Claroline\CursusBundle\Entity\SessionEventUser seu
            JOIN seu.sessionEvent se
            JOIN seu.user u
            WHERE se = :sessionEvent
            AND seu.registrationStatus = :status
            AND u IN (:users)
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sessionEvent', $sessionEvent);
        $query->setParameter('status', $status);
        $query->setParameter('users', $users);

        return $query->getResult();
    }

    public function findSessionEventUsersByUserAndSession(User $user, CourseSession $session)
    {
        $dql = '
            SELECT seu
            FROM Claroline\CursusBundle\Entity\SessionEventUser seu
            JOIN seu.sessionEvent se
            JOIN se.session s
            JOIN seu.user u
            WHERE s = :session
            AND u = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSessionEventUsersByUserAndSessionAndStatus(User $user, CourseSession $session, $status)
    {
        $dql = '
            SELECT seu
            FROM Claroline\CursusBundle\Entity\SessionEventUser seu
            JOIN seu.sessionEvent se
            JOIN se.session s
            JOIN seu.user u
            WHERE s = :session
            AND u = :user
            AND seu.registrationStatus = :status
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('status', $status);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSessionEventUsersByUserAndEventSet(User $user, SessionEventSet $eventSet)
    {
        $dql = '
            SELECT seu
            FROM Claroline\CursusBundle\Entity\SessionEventUser seu
            JOIN seu.sessionEvent se
            JOIN se.eventSet ses
            JOIN seu.user u
            WHERE ses = :eventSet
            AND u = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eventSet', $eventSet);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}
