<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class WorkspaceRegistrationQueueRepository extends EntityRepository
{
    public function findByWorkspace(Workspace $workspace)
    {
        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue w
            WHERE w.workspace = :workspace
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

    public function findByUser(User $user)
    {
        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue w
            WHERE w.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findOneByWorkspaceAndUser(Workspace $workspace, User $user)
    {
        $dql = '
            SELECT q
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue q
            WHERE q.workspace = :workspace
            AND q.user = :user
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('user', $user);

        return $query->getOneOrNullResult();
    }

    public function findByWorkspaceAndSearch(Workspace $workspace, $search = '')
    {
        $dql = "
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue w
            JOIN w.user u
            WHERE w.workspace = :workspace
            AND (
                UPPER(u.username) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR UPPER(u.mail) LIKE :search
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}
