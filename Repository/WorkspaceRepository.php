<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceRepository extends EntityRepository
{
    /**
     * Returns the workspaces a user is member of.
     *
     * @param User $user
     *
     * @return array[AbstractWorkspace]
     */
    public function findByUser(User $user)
    {
        $dql = '
            SELECT w, r FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are not a user's personal workspace.
     *
     * @return array[AbstractWorkspace]
     */
    public function findNonPersonal()
    {
        $dql = '
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            WHERE w.id NOT IN (
                SELECT w1.id FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w1
                JOIN w1.personalUser pu
            )
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces whose at least one tool is accessible to anonymous users.
     *
     * @return array[AbstractWorkspace]
     */
    public function findByAnonymous()
    {
        $dql = "
            SELECT DISTINCT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.orderedTools ot
            JOIN ot.roles r
            WHERE r.name = 'ROLE_ANONYMOUS'
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Counts the workspaces.
     *
     * @return integer
     */
    public function count()
    {
        $dql = 'SELECT COUNT(w) FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w';
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    /**
     * Returns the workspaces whose at least one tool is accessible to one of the given roles.
     *
     * @param array[string] $roles
     *
     * @return array[AbstractWorkspace]
     */
    public function findByRoles(array $roles)
    {
        $dql = "
            SELECT DISTINCT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.orderedTools ot
            JOIN ot.roles r
            WHERE r.name = '{$roles[0]}'
        ";

        for ($i = 1, $size = count($roles); $i < $size; $i++) {
            $dql .= " OR r.name = '{$roles[$i]}'";
        }

        $dql .= "
            ORDER BY w.name
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the ids of the workspaces a user is member of, filtered by a set of roles
     * the user must have in those workspaces. Role names are actually prefixes of the
     * target role (e.g. 'ROLE_WS_COLLABORATOR' instead of 'ROLE_WS_COLLABORATOR_123').
     *
     * @param User          $user
     * @param array[string] $roleNames
     *
     * @return array
     */
    public function findIdsByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->doFindByUserAndRoleNames($user, $roleNames, true);
    }

    /**
     * Returns the workspaces a user is member of, filtered by a set of roles the user
     * must have in those workspaces. Role names are actually prefixes of the target
     * role (e.g. 'ROLE_WS_COLLABORATOR' instead of 'ROLE_WS_COLLABORATOR_123').
     *
     * @param User          $user
     * @param array[string] $roleNames
     *
     * @return array[AbstractWorkspace]
     */
    public function findByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->doFindByUserAndRoleNames($user, $roleNames);
    }

    /**
     * Returns the workspaces a user is member of, filtered by a set of roles the user
     * must have in those workspaces, and optionnaly excluding workspaces by id. Role
     * names are actually prefixes of the target role (e.g. 'ROLE_WS_COLLABORATOR'
     * instead of 'ROLE_WS_COLLABORATOR_123').
     *
     * @param User           $user
     * @param array[string]  $roleNames
     * @param array[integer] $restrictionIds
     *
     * @return array[AbstractWorkspace]
     */
    public function findByUserAndRoleNamesNotIn(User $user, array $roleNames, array $restrictionIds = null)
    {
        if ($restrictionIds === null || count($restrictionIds) === 0) {
            return $this->findByUserAndRoleNames($user, $roleNames);
        }

        $rolesRestriction = '';
        $first = true;

        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "(r.name like '{$roleName}_%'";
            } else {
                $rolesRestriction .= " OR r.name like '{$roleName}_%'";
            }
        }

        $rolesRestriction .= ')';
        $dql = "
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND {$rolesRestriction}
            AND w.id NOT IN (:restrictionIds)
            ORDER BY w.name
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('restrictionIds', $restrictionIds);

        return $query->getResult();
    }

    /**
     * Returns the latest workspaces a user has visited.
     *
     * @param User          $user
     * @param array[string] $roles
     * @param integer       $max
     *
     * @return array
     */
    public function findLatestWorkspacesByUser(User $user, array $roles, $max = 5)
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

        $dql .= ')
            GROUP BY w.id
            ORDER BY max_date DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setMaxResults($max);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Returns the name, code and number of resources of each workspace.
     *
     * @param integer $max
     *
     * @return array
     */
    public function findWorkspacesWithMostResources($max)
    {
        $qb = $this
            ->createQueryBuilder('ws')
            ->select('ws.name, ws.code, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\AbstractResource', 'rs', 'WITH', 'ws = rs.workspace')
            ->groupBy('ws.id')
            ->orderBy('total', 'DESC');

        if ($max > 1) {
            $qb->setMaxResults($max);
        }

        return $qb->getQuery()->getResult();
    }

    private function doFindByUserAndRoleNames(User $user, array $roleNames, $idsOnly = false)
    {
        $rolesRestriction = '';
        $first = true;

        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "(r.name like '{$roleName}_%'";
            } else {
                $rolesRestriction .= " OR r.name like '{$roleName}_%'";
            }
        }

        $rolesRestriction .= ')';
        $select = $idsOnly ? 'w.id' : 'w';
        $dql = "
            SELECT {$select} FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE u.id = :userId
            AND {$rolesRestriction}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible for each user.
     *
     * @return array[AbstractWorkspace]
     */
    public function findDisplayableWorkspaces()
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            WHERE w.displayable = true
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible for each user
     * and allowing self-registration.
     *
     * @return array[AbstractWorkspace]
     */
    public function findWorkspacesWithSelfRegistration()
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            WHERE w.displayable = true
            AND w.selfRegistration = true
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible for each user
     * and where name or code contains $search param.
     *
     * @return array[AbstractWorkspace]
     */
    public function findDisplayableWorkspacesBySearch($search)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
            WHERE w.displayable = true
            AND (
                UPPER(w.name) LIKE :search
                OR UPPER(w.code) LIKE :search
            )
            ORDER BY w.name
        ';
        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return $query->getResult();
    }
}
