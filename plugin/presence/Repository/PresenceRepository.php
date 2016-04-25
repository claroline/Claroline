<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FormaLibre\PresenceBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PresenceRepository extends EntityRepository
{
    public function OrderByNumPeriod($session, $date)
    {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.period pp
            WHERE p.courseSession = (:session)
            AND p.date =(:date)
            ORDER BY pp.numPeriod ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('date', $date);

        return $query->getResult();
    }

    public function OrderByStudent($session, $date, $period)
    {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.userStudent u
            WHERE p.courseSession = (:session)
            AND p.date =(:date)
            AND p.period =(:period)
            ORDER BY u.lastName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('date', $date);
        $query->setParameter('period', $period);

        return $query->getResult();
    }

    public function findBySchoolYear($year)
    {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.period pe
            JOIN pe.schoolYearId s
            WHERE s.schoolYearName = :year
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('year', $year->getSchoolYearName());

        return $query->getResult();
    }

    public function findPresencesByUserAndSession(User $user, array $sessions)
    {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.userStudent pu
            JOIN p.courseSession pcs
            WHERE pu = :user
            AND pcs IN (:sessions)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('sessions', $sessions);

        return $query->getResult();
    }

    public function findPresencesByUserAndSessionAndStatusName(
        User $user,
        array $sessions,
        $statusName
    ) {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.userStudent pu
            JOIN p.courseSession pcs
            JOIN p.status ps
            WHERE pu = :user
            AND pcs IN (:sessions)
            AND ps.statusName = :statusName
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('sessions', $sessions);
        $query->setParameter('statusName', $statusName);

        return $query->getResult();
    }
}
