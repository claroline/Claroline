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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Doctrine\ORM\EntityRepository;

class SessionEventRepository extends EntityRepository
{
    public function findEventsBySession(CourseSession $session, $orderedBy = 'startDate', $order = 'ASC', $executeQuery = true)
    {
        $dql = "
            SELECT se
            FROM Claroline\CursusBundle\Entity\SessionEvent se
            WHERE se.session = :session
            ORDER BY se.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionEventsBySessionAndUserAndRegistrationStatus(
        CourseSession $session,
        User $user,
        $registrationStatus,
        $orderedBy = 'startDate',
        $order = 'ASC'
    ) {
        $dql = "
            SELECT se
            FROM Claroline\CursusBundle\Entity\SessionEvent se
            JOIN se.session s
            WHERE s = :session
            AND EXISTS (
                SELECT seu
                FROM Claroline\CursusBundle\Entity\SessionEventUser seu
                JOIN seu.sessionEvent seuse
                JOIN seu.user seuu
                WHERE seuse = se
                AND seuu = :user
                AND seu.registrationStatus = :registrationStatus
            )
            ORDER BY se.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);
        $query->setParameter('registrationStatus', $registrationStatus);

        return $query->getResult();
    }

    public function findSessionEventsByWorkspace(Workspace $workspace)
    {
        $dql = '
            SELECT se
            FROM Claroline\CursusBundle\Entity\SessionEvent se
            JOIN se.session s
            JOIN s.workspace w
            WHERE w = :workspace
            AND se.type = :eventType
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('eventType', SessionEvent::TYPE_EVENT);

        return $query->getResult();
    }

    public function findSessionEventsByUser(User $user)
    {
        $dql = "
            SELECT se
            FROM Claroline\CursusBundle\Entity\SessionEvent se
            WHERE EXISTS (
                SELECT seu
                FROM Claroline\CursusBundle\Entity\SessionEventUser seu
                JOIN seu.sessionEvent seuse
                JOIN seu.user seuu
                WHERE seuse = se
                AND seuu = :user
                AND seu.registrationStatus = :registered
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('registered', SessionEventUser::REGISTERED);

        return $query->getResult();
    }
}
