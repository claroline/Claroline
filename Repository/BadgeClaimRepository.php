<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class BadgeClaimRepository extends EntityRepository
{
    /**
     * @param User $user
     *
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

        return ($getQuery) ? $query: $query->getResult();
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
     *
     * @param bool $executedQuery
     *
     * @return \Doctrine\ORM\Query|array
     */
    public function findByWorkspace(Workspace $workspace = null, $executedQuery = true)
    {
        $workspaceConstraint = 'b.workspace = :workspace';

        if (null === $workspace) {
            $workspaceConstraint = 'b.workspace IS NULL';
        }

        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT bc, b, bt
                FROM IcapBadgeBundle:BadgeClaim bc
                JOIN bc.badge b
                JOIN b.translations bt
                WHERE ' . $workspaceConstraint
            );

        if (null !== $workspace) {
            $query->setParameter('workspace', $workspace);
        }

        return ($executedQuery) ? $query->getResult(): $query;
    }
}
