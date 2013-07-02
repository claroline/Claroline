<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceRepository extends EntityRepository
{
    public function findByUser(User $user)
    {
        $dql = "
            SELECT w, r FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findNonPersonal()
    {
        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            WHERE w.id NOT IN (
                SELECT w1.id FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w1
                JOIN w1.personalUser pu
            )
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findByAnonymous()
    {
        $dql = "
            SELECT DISTINCT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.orderedTools ot
            JOIN ot.roles r
            WHERE r.name = 'ROLE_ANONYMOUS'";

            $query = $this->_em->createQuery($dql);

            return $query->getResult();
    }

    public function count()
    {
        $dql = "SELECT COUNT(w) FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    public function findByRoles(array $roles)
    {
        $dql = "
            SELECT DISTINCT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.orderedTools ot
            JOIN ot.roles r
            WHERE r.name = '{$roles[0]}'";

        for ($i = 1, $size = count($roles); $i < $size; $i++) {
            $dql .= " OR r.name = '{$roles[$i]}'";
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findIdsByUserAndRoleNames($user, $roleNames)
    {
        $rolesRestriction = "";
        $first = true;
        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "( r.name like '".$roleName."_%'";
            } else {
                $rolesRestriction .= "OR r.name like '".$roleName."_%'";
            }
        }
        $rolesRestriction .= " )";

        $dql = "
            SELECT w.id FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND ".$rolesRestriction;

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findByUserAndRoleNames($user, $roleNames)
    {
        $rolesRestriction = "";
        $first = true;
        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "( r.name like '".$roleName."_%'";
            } else {
                $rolesRestriction .= "OR r.name like '".$roleName."_%'";
            }
        }
        $rolesRestriction .= " )";

        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND ".$rolesRestriction."
            ORDER BY w.name";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findByUserAndRoleNamesNotIn($user, $roleNames, $restrictionIds)
    {
        if ($restrictionIds === null || count($restrictionIds) == 0) {

            return $this->findByUserAndRoleNames($user, $roleNames);
        }

        $rolesRestriction = "";
        $first = true;
        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "( r.name like '".$roleName."_%'";
            } else {
                $rolesRestriction .= "OR r.name like '".$roleName."_%'";
            }
        }
        $rolesRestriction .= " )";

        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND ".$rolesRestriction."
            AND w.id NOT IN (:restrictionIds)
            ORDER BY w.name";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('restrictionIds', $restrictionIds);

        return $query->getResult();
    }

    public function findLatestWorkspaceByUser(User $user, array $roles, $size = 5)
    {
        $dql = "
            SELECT DISTINCT w AS workspace, MAX(l.dateLog) AS max_date
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            INNER JOIN Claroline\CoreBundle\Entity\Logger\Log l WITH l.workspace = w
            JOIN l.doer u
            JOIN w.roles r
            WHERE l.action = 'ws_tool_read'
            AND u.id = :userId
            AND (
        ";

        $index = 0;
        $eol = PHP_EOL;

        foreach ($roles as $role) {
            $dql .= $index > 0 ? '    OR ' : '    ';
            $dql .= "r.name = '{$role}'{$eol}";
            $index++;
        }
        $dql .= ")";
        $dql .= "
            GROUP BY w.id
            ORDER BY max_date DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setMaxResults($size);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function workspacesWithMostResources($max)
    {
        $qb = $this
            ->createQueryBuilder('ws')
            ->select('ws.name, ws.code, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\AbstractResource','rs','WITH','ws = rs.workspace')
            ->groupBy('ws.id')
            ->orderBy('total','DESC');

        if ($max > 1)
        {
            $qb->setMaxResults($max);
        }
        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }
}