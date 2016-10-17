<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Repository;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class AnnouncementRepository extends EntityRepository
{
    public function findVisibleByWorkspace(Workspace $workspace)
    {
        $dql = '
            SELECT a AS announcement,
                   w.id AS workspaceId,
                   w.name AS workspaceName,
                   w.code AS workspaceCode,
                   n.id AS resourceNodeId
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            JOIN aa.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE w = :workspace
            AND a.visible = true
            AND ((a.visibleFrom IS NULL) OR (a.visibleFrom <= :now))
            AND ((a.visibleUntil IS NULL) OR (a.visibleUntil >= :now))
            ORDER BY a.publicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }

    public function findVisibleByWorkspaceAndRoles(Workspace $workspace, array $roles)
    {
        $dql = '
            SELECT a AS announcement,
                   w.id AS workspaceId,
                   w.name AS workspaceName,
                   w.code AS workspaceCode,
                   n.id AS resourceNodeId
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            JOIN aa.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE w = :workspace
            AND a.visible = true
            AND ((a.visibleFrom IS NULL) OR (a.visibleFrom <= :now))
            AND ((a.visibleUntil IS NULL) OR (a.visibleUntil >= :now))
            AND BIT_AND(r.mask, 1) = 1
            AND rr.name in (:roles)
            ORDER BY a.publicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('roles', $roles);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }

    public function findVisibleByWorkspacesAndRoles(array $workspaces, array $managerWorkspaces, array $roles)
    {
        $dql = '
            SELECT a AS announcement,
                   w.id AS workspaceId,
                   w.name AS workspaceName,
                   w.code AS workspaceCode,
                   n.id AS resourceNodeId
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            JOIN aa.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE a.visible = true
            AND ((a.visibleFrom IS NULL) OR (a.visibleFrom <= :now))
            AND ((a.visibleUntil IS NULL) OR (a.visibleUntil >= :now))
            AND (
              w IN (:managerWorkspaces)
              OR (
                w IN (:workspaces)
                AND BIT_AND(r.mask, 1) = 1
              )
            )
            AND rr.name in (:roles)
            ORDER BY a.publicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('managerWorkspaces', $managerWorkspaces);
        $query->setParameter('roles', $roles);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }

    public function findAllAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        $dql = '
            SELECT a
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            WHERE aa = :aggregate
            ORDER BY a.creationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('aggregate', $aggregate);

        return $query->getResult();
    }

    public function findVisibleAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        $now = new \DateTime();

        $dql = '
            SELECT a
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            WHERE aa = :aggregate
            AND a.visible = true
            AND ( ( a.visibleFrom IS NULL ) OR ( a.visibleFrom <= :now ) )
            AND ( ( a.visibleUntil IS NULL ) OR ( a.visibleUntil >= :now ) )
            ORDER BY a.creationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('aggregate', $aggregate);
        $query->setParameter('now', $now);

        return $query->getResult();
    }
}
