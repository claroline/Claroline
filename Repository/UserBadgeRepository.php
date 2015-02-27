<?php

namespace Icap\BadgeBundle\Repository;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class UserBadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return \Claroline\CoreBundle\Entity\Badge\UserBadge|null
     */
    public function findOneByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->findOneBy(array('badge' => $badge, 'user' => $user));
    }

    /**
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, b, bt
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                JOIN b.translations bt
                WHERE ub.user = :userId'
            )
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param integer[] $userIds
     *
     * @param bool $executeQuery
     *
     * @return \Doctrine\ORM\Query|\Icap\BadgeBundle\Entity\UserBadge[]
     */
    public function findByUserIds(array $userIds, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT userBadge, badge, badgeTranslation
                FROM IcapBadgeBundle:UserBadge userBadge
                JOIN userBadge.badge badge
                JOIN badge.translations badgeTranslation
                WHERE userBadge.user IN (:userIds)'
            )
            ->setParameter('userIds', $userIds);

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param Workspace $workspace
     *
     * @return integer
     */
    public function countAwardingByWorkspace(Workspace $workspace)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(ub.id)
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE b.workspace = :workspaceId'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query->getSingleScalarResult();
    }

    /**
     * @param Workspace $workspace
     *
     * @return integer
     */
    public function countAwardedBadgeByWorkspace(Workspace $workspace)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(DISTINCT b.id)
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE b.workspace = :workspaceId'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query->getSingleScalarResult();
    }

    /**
     * @param Workspace $workspace
     * @param int       $limit
     *
     * @return Badge[]
     */
    public function findWorkspaceLastAwardedBadges(Workspace $workspace, $limit = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, b
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE b.workspace = :workspaceId
                ORDER BY ub.issuedAt DESC'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query
                ->setMaxResults($limit)
                ->getResult();
    }

    /**
     * @param Workspace $workspace
     * @param int       $limit
     *
     * @return Badge[]
     */
    public function findWorkspaceMostAwardedBadges(Workspace $workspace, $limit = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, b, COUNT(ub) AS awardedNumber
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE b.workspace = :workspaceId
                GROUP BY ub.badge
                ORDER BY awardedNumber DESC'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query
                ->setMaxResults($limit)
                ->getResult();
    }
}
