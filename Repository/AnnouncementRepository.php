<?php

namespace Claroline\AnnouncementBundle\Repository;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;

class AnnouncementRepository extends EntityRepository
{
    public function findVisibleAnnouncementsByWorkspace(AbstractWorkspace $workspace, array $roles)
    {
        $now = new \DateTime();

        $dql = '
            SELECT a AS announcement
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            JOIN aa.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE w = :workspace
            AND a.visible = true
            AND ( ( a.visibleFrom IS NULL ) OR ( a.visibleFrom <= :now ) )
            AND ( ( a.visibleUntil IS NULL ) OR ( a.visibleUntil >= :now ) )
            AND r.canOpen = true
            AND rr.name in (:roles)
            ORDER BY a.publicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('roles', $roles);
        $query->setParameter('now', $now);

        return $query->getResult();
    }

    public function findVisibleAnnouncementsByWorkspaces(array $workspaces, array $roles)
    {
        $dql = '
            SELECT
                a AS announcement,
                w.id AS workspaceId,
                w.name AS workspaceName,
                w.code AS workspaceCode
            FROM Claroline\AnnouncementBundle\Entity\Announcement a
            JOIN a.aggregate aa
            JOIN aa.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE w IN (:workspaces)
            AND a.visible = true
            AND ( ( a.visibleFrom IS NULL ) OR ( a.visibleFrom <= :now ) )
            AND ( ( a.visibleUntil IS NULL ) OR ( a.visibleUntil >= :now ) )
            AND r.canOpen = true
            AND rr.name in (:roles)
            ORDER BY a.publicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('roles', $roles);

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

        return $query->getResult();
    }
}