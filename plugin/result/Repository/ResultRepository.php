<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class ResultRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the results associated
     * with a user in a given workspace.
     *
     * @param User      $user
     * @param Workspace $workspace
     *
     * @return array
     */
    public function findByUserAndWorkspace(User $user, Workspace $workspace)
    {
        $dql = '
            SELECT
                n.name AS title,
                m.value AS mark,
                r.total AS total
            FROM Claroline\ResultBundle\Entity\Result r
            JOIN r.resourceNode n
            JOIN n.workspace w
            JOIN r.marks m
            JOIN m.user u
            WHERE w = :workspace
            AND u = :user
            ORDER BY n.creationDate DESC, n.id
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        return $query->getArrayResult();
    }

    /**
     * Returns an array representation of all the results associated with a user.
     *
     * @param User $user
     *
     * @return array
     */
    public function findByUser(User $user)
    {
        $dql = '
            SELECT
                n.name AS title,
                m.value AS mark,
                r.total AS total
            FROM Claroline\ResultBundle\Entity\Result r
            JOIN r.resourceNode n
            JOIN r.marks m
            JOIN m.user u
            WHERE u = :user
            ORDER BY n.creationDate DESC, n.id
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getArrayResult();
    }
}
