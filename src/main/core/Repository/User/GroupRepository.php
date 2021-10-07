<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\User;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository
{
    /**
     * Returns the groups which are member of a workspace.
     *
     * @return Group[]
     */
    public function findByWorkspace(Workspace $workspace)
    {
        return $this->_em
            ->createQuery('
                SELECT g, wr
                FROM Claroline\CoreBundle\Entity\Group g
                LEFT JOIN g.roles wr WITH wr IN (
                    SELECT pr from Claroline\CoreBundle\Entity\Role pr WHERE pr.type = :type
                )
                LEFT JOIN wr.workspace w
                WHERE w.id = :workspaceId
           ')
            ->setParameter('workspaceId', $workspace->getId())
            ->setParameter('type', Role::WS_ROLE)
            ->getResult();
    }

    /**
     * @return Group[]
     */
    public function findByOrganizations(array $organizations = [])
    {
        if (!empty($organizations)) {
            return $this->_em
                ->createQuery('
                    SELECT g
                    FROM Claroline\CoreBundle\Entity\Group g
                    JOIN g.organizations AS og
                    WHERE og IN (:organizations)
               ')
                ->setParameter('organizations', $organizations)
                ->getResult();
        }

        return $this->findAll();
    }

    public function findByRoles(array $roles, $getQuery = false, $orderedBy = 'id', $order = null)
    {
        $dql = "
            SELECT u, ws, r FROM Claroline\\CoreBundle\\Entity\\Group u
            JOIN u.roles r
            LEFT JOIN r.workspace ws
            WHERE r IN (:roles)
            ORDER BY u.{$orderedBy}
            ".$order;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return ($getQuery) ? $query : $query->getResult();
    }

    /**
     * Returns groups by their names.
     *
     * @return Group[]
     */
    public function findByNames(array $names)
    {
        $dql = '
            SELECT g FROM Claroline\CoreBundle\Entity\Group g
            WHERE g.name IN (:names)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('names', $names);

        $result = $query->getResult();

        return $result;
    }

    public function countGroupsByRole(Role $role)
    {
        $qb = $this->createQueryBuilder('grp')
            ->select('COUNT(DISTINCT grp.id)')
            ->leftJoin('grp.roles', 'roles')
            ->andWhere('roles.id = :roleId')
            ->setParameter('roleId', $role->getId());
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }
}
