<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Icap\BadgeBundle\Entity\Badge;

class BadgeClaimRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param bool $getQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $getQuery = false)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT bc, b, bt
                FROM IcapBadgeBundle:BadgeClaim bc
                JOIN bc.badge b
                JOIN b.translations bt
                WHERE bc.user = :userId'
            )
            ->setParameter('userId', $user->getId());

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return \Icap\badgeBundle\Entity\BadgeClaim|null
     */
    public function findOneByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->findOneBy(array('badge' => $badge, 'user' => $user));
    }

    /**
     * @return \Icap\BadgeBundle\Entity\BadgeClaim[]
     */
    public function findAll()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT bc, b, bt
                FROM IcapBadgeBundle:BadgeClaim bc
                JOIN bc.badge b
                JOIN b.translations bt'
            )
            ->getResult();
    }

    /**
     * @param Workspace $workspace
     * @param bool      $executedQuery
     *
     * @return \Doctrine\ORM\Query|array
     */
    public function findByWorkspace(Workspace $workspace = null, $executedQuery = true)
    {
        $qb = $this->createQueryBuilder('bc')
            ->select('bc', 'b', 'bt')
            ->join('bc.badge', 'b')
            ->join('b.translations', 'bt');

        if ($workspace) {
            $qb->where('b.workspace = :workspace');
            $qb->setParameter('workspace', $workspace);
        } else {
            $qb->where('b.workspace IS NULL');
        }

        $query = $qb->getQuery();

        return $executedQuery ? $query->getResult() : $query;
    }
}
