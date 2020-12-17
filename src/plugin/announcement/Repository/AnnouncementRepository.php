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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class AnnouncementRepository extends EntityRepository
{
    public function findVisibleByWorkspace(Workspace $workspace)
    {
        return $this->_em
            ->createQuery('
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
            ')
            ->setParameters([
                'workspace' => $workspace,
                'now' => new \DateTime(),
            ])
            ->getResult();
    }

    public function findVisibleByWorkspaceAndRoles(Workspace $workspace, array $roles)
    {
        return $this->_em
            ->createQuery('
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
            ')
            ->setParameters([
                'workspace' => $workspace,
                'roles' => $roles,
                'now' => new \DateTime(),
            ])
            ->getResult();
    }

    public function findVisibleByWorkspacesAndRoles(array $workspaces, array $managerWorkspaces, array $roles)
    {
        return $this->_em
            ->createQuery('
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
            ')
            ->setParameters([
                'workspaces' => $workspaces,
                'managerWorkspaces' => $managerWorkspaces,
                'roles' => $roles,
                'now' => new \DateTime(),
            ])
            ->getResult();
    }
}
