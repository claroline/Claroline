<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Doctrine\ORM\EntityRepository;

class DropRepository extends EntityRepository
{
    public function findTeamDrops(Dropzone $dropzone, User $user)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.users uu
            WHERE d = :dropzone
            AND uu = :user
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findUserFinishedPeerDrops(Dropzone $dropzone, User $user)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.corrections c
            JOIN drop.user u
            JOIN c.user cu
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NULL
            AND c.finished = true
            AND u != :user
            AND cu = :user
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findTeamFinishedPeerDrops(Dropzone $dropzone, $teamId)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.corrections c
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NOT NULL
            AND drop.teamUuid != :teamId
            AND c.finished = true
            AND c.teamUuid = :teamId
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('teamId', $teamId);

        return $query->getResult();
    }

    public function findUserUnfinishedPeerDrop(Dropzone $dropzone, User $user)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.corrections c
            JOIN drop.user u
            JOIN c.user cu
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NULL
            AND c.finished = false
            AND u != :user
            AND cu = :user
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findTeamUnfinishedPeerDrop(Dropzone $dropzone, $teamId)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.corrections c
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NOT NULL
            AND drop.teamUuid != :teamId
            AND c.finished = false
            AND c.teamUuid = :teamId
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('teamId', $teamId);

        return $query->getResult();
    }

    public function findUserAvailableDrops(Dropzone $dropzone, User $user)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            JOIN drop.user u
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NULL
            AND u != :user
            AND NOT EXISTS (
              SELECT cor
              FROM Claroline\DropZoneBundle\Entity\Correction cor
              JOIN cor.drop cd
              JOIN cor.user coru
              WHERE cd = drop
              AND coru = :user
              AND cor.teamUuid IS NULL
            )
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findTeamAvailableDrops(Dropzone $dropzone, $teamId)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            WHERE d = :dropzone
            AND drop.finished = true
            AND drop.teamUuid IS NOT NULL
            AND drop.teamUuid != :teamId
            AND NOT EXISTS (
              SELECT cor
              FROM Claroline\DropZoneBundle\Entity\Correction cor
              JOIN cor.drop cd
              WHERE cd = drop
              AND cor.teamUuid = :teamId
            )
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('teamId', $teamId);

        return $query->getResult();
    }

    public function findTeamsUnfinishedDrops(Dropzone $dropzone, array $teamsIds)
    {
        $dql = '
            SELECT drop
            FROM Claroline\DropZoneBundle\Entity\Drop drop
            JOIN drop.dropzone d
            WHERE d = :dropzone
            AND drop.finished = false
            AND drop.teamUuid IN (:teamsIds)
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('dropzone', $dropzone);
        $query->setParameter('teamsIds', $teamsIds);

        return $query->getResult();
    }
}
