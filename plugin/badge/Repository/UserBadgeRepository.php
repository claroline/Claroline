<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Icap\BadgeBundle\Entity\Badge;

class UserBadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return \Icap\badgeBundle\Entity\UserBadge|null
     */
    public function findOneByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->findOneBy(['badge' => $badge, 'user' => $user]);
    }

    /**
     * @param User $user
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param int[] $userIds
     * @param bool  $executeQuery
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param Workspace $workspace
     *
     * @return int
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
     * @return int
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
     * @param User      $user
     * @param int       $limit
     *
     * @return \Icap\BadgeBundle\Entity\Badge[]
     */
    public function findWorkspaceLastAwardedBadgesToUser(Workspace $workspace, User $user, $limit = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, b
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE ub.user = :user
                AND b.workspace = :workspaceId
                ORDER BY ub.issuedAt DESC'
            )
            ->setParameter('workspaceId', $workspace->getId())
            ->setParameter('user', $user->getId());

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
                'SELECT ub AS userBadge, b, COUNT (ub) AS awardedNumber
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE b.workspace = :workspaceId
                GROUP BY b
                ORDER BY awardedNumber DESC'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query
                ->setMaxResults($limit)
                ->getResult();
    }

    /**
     * @param Workspace $workspace
     *
     * @return Badge[]
     */
    public function countBadgesPerUser(Workspace $workspace)
    {
        $sql = 'SELECT qr1.nb_badge AS nb_badge, COUNT(qr1.id) AS nb_user
                FROM (SELECT userBadge.user_id AS id, COUNT(userBadge.id) AS nb_badge
                FROM claro_user_badge AS userBadge
                LEFT JOIN claro_badge AS badge ON userBadge.badge_id = badge.id
                WHERE badge.workspace_id = :workspaceId
                GROUP BY userBadge.user_id) AS qr1
                GROUP BY qr1.nb_badge
                ORDER BY qr1.nb_badge';

        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('nb_user', 'nb_user')
            ->addScalarResult('nb_badge', 'nb_badge');

        $query = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('workspaceId', $workspace->getId());

        return $query->getResult('PairHydrator');
    }

    /**
     * @param Badge $badge
     * @param bool  $executeQuery
     *
     * @return Query|array
     */
    public function findByBadge(Badge $badge, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT userBadge
                FROM IcapBadgeBundle:UserBadge userBadge
                WHERE userBadge.badge = :badgeId'
            )
            ->setParameter('badgeId', $badge->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param string $badgeSlug
     * @param bool   $executeQuery
     *
     * @return Query|array
     */
    public function findOneByBadgeSlug($badgeSlug, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT userBadge, badge, badgeTranslation
                FROM IcapBadgeBundle:UserBadge userBadge
                JOIN userBadge.badge badge
                JOIN badge.translations badgeTranslation
                WHERE badgeTranslation.slug = :badgeSlug
            ')
            ->setParameter('badgeSlug', $badgeSlug);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    /**
     * @param string $username
     * @param string $badgeSlug
     * @param bool   $executeQuery
     *
     * @return Query|array
     */
    public function findOneByUsernameAndBadgeSlug($username, $badgeSlug, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT userBadge, badge, badgeTranslation
                FROM IcapBadgeBundle:UserBadge userBadge
                JOIN userBadge.badge badge
                JOIN userBadge.user usr
                JOIN badge.translations badgeTranslation
                WHERE badgeTranslation.slug = :badgeSlug
                AND usr.username = :username
            ')
            ->setParameter('badgeSlug', $badgeSlug)
            ->setParameter('username', $username);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findUsersNotAwardedWithBadge(Badge $badge)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $notIn = $qb->select('IDENTITY(ub.user)')
                        ->from('IcapBadgeBundle:Userbadge', 'ub')
                        ->where('ub.badge = ?1');

        $qb1 = $em->createQueryBuilder();
        $qb1->select('u')
                ->from('ClarolineCoreBundle:User', 'u')
                ->where($qb1->expr()->notIn('u.id', $notIn->getDQL()))
                ->setParameter(1, $badge->getId());

        return $qb1->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param int  $limit
     *
     * @return Badge[]
     */
    public function findUserLastAwardedBadges(User $user, $limit = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, b
                FROM IcapBadgeBundle:UserBadge ub
                JOIN ub.badge b
                WHERE ub.user = :userId
                ORDER BY ub.issuedAt DESC'
            )
            ->setParameter('userId', $user->getId());

        return $query->setMaxResults($limit)->getResult();
    }
}
