<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class AssertionRepository extends EntityRepository
{
    public function countUserBadges(User $user, Workspace $workspace = null): int
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.recipient = :user')
            ->setParameter('user', $user);

        if ($workspace) {
            $qb
                ->join('a.badge', 'b')
                ->andWhere('b.workspace = :workspace')
                ->setParameter('workspace', $workspace);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
