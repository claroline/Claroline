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

use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class WorkspaceRepository extends EntityRepository
{
    /**
     * Returns the workspaces a user is member of.
     *
     * @param User $user
     *
     * @return Workspace[]
     */
    public function findByUser(User $user)
    {
        return $this->_em
            ->createQuery('
                SELECT w, r FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                JOIN w.roles r
                JOIN r.users u
                WHERE u.id = :userId
            ')
            ->setParameter('userId', $user->getId())
            ->getResult();
    }

    /**
     * Counts the workspaces.
     *
     * @return int
     */
    public function countWorkspaces()
    {
        return $this->_em
            ->createQuery('
                SELECT COUNT(w)
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            ')
            ->getSingleScalarResult();
    }

    /**
     * Counts the personal workspaces.
     *
     * @return int
     */
    public function countPersonalWorkspaces()
    {
        return $this->_em
            ->createQuery('
                SELECT COUNT(w)
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE w.personal = true
            ')
            ->getSingleScalarResult();
    }

    /**
     * Counts the non personal workspaces.
     *
     * @return int
     */
    public function countNonPersonalWorkspaces($organizations = null)
    {
        $qb = $this
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->andWhere('w.personal = :personal')
            ->setParameter('personal', false);
        if (null !== $organizations) {
            $qb->join('w.organizations', 'orgas')
                ->andWhere('orgas IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns the workspaces whose at least one tool is accessible to one of the given roles.
     *
     * @param string[] $roles
     *
     * @return Workspace[]
     */
    public function findByRoles(array $roles)
    {
        return $this->_em
            ->createQuery('
                SELECT DISTINCT w FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.roles r
                WHERE r.name in (:roles)
                ORDER BY w.name
            ')
            ->setParameter('roles', $roles)
            ->getResult();
    }

    /**
     * Finds which workspaces can be opened by one of the given roles,
     * in a given set of workspaces. If a tool name is passed in, the
     * check will be limited to that tool, otherwise workspaces with
     * at least one accessible tool will be considered open. Only the
     * ids are returned.
     *
     * @param array       $roleNames
     * @param Workspace[] $workspaces
     * @param string|null $toolName
     * @param string      $action
     * @param int         $orderedToolType
     *
     * @return int[]
     */
    public function findOpenWorkspaceIds(
        array $roleNames,
        array $workspaces,
        $toolName = null,
        $action = 'open',
        $orderedToolType = 0
    ) {
        if (empty($roleNames) || empty($workspaces)) {
            return [];
        }

        $dql = '
                SELECT DISTINCT w.id
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                JOIN w.orderedTools ot
                JOIN ot.tool t
                JOIN ot.rights r
                JOIN r.role rr
                WHERE w IN (:workspaces)
                AND rr.name IN (:roleNames)
                AND ot.type = :type
                AND EXISTS (
                    SELECT d
                    FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder d
                    WHERE d.tool = t
                    AND d.name = :action
                    AND BIT_AND(r.mask, d.value) = d.value
                )
            ';

        if ($toolName) {
            $dql .= 'AND t.name = :toolName';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('roleNames', $roleNames);
        $query->setParameter('action', $action);
        $query->setParameter('type', $orderedToolType);

        if ($toolName) {
            $query->setParameter('toolName', $toolName);
        }

        return $query->getResult();
    }

    /**
     * Returns the workspaces a user is member of, filtered by a set of roles the user
     * must have in those workspaces. Role names are actually prefixes of the target
     * role (e.g. 'ROLE_WS_COLLABORATOR' instead of 'ROLE_WS_COLLABORATOR_123').
     *
     * @param User     $user
     * @param string[] $roleNames
     *
     * @return Workspace[]
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
     * @param User     $user
     * @param string[] $roleNames
     * @param int[]    $restrictionIds
     *
     * @return Workspace[]
     */
    public function findByUserAndRoleNamesNotIn(User $user, array $roleNames, array $restrictionIds = null)
    {
        if (null === $restrictionIds || 0 === count($restrictionIds)) {
            return $this->findByUserAndRoleNames($user, $roleNames);
        }

        $rolesRestriction = '';
        $first = true;

        foreach ($roleNames as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "(r.name LIKE '{$roleName}_%'";
            } else {
                $rolesRestriction .= " OR r.name LIKE '{$roleName}_%'";
            }
        }

        $rolesRestriction .= ')';
        $dql = "
            SELECT w FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
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
     * @param int           $max
     *
     * @return array
     */
    public function findLatestWorkspacesByUser(User $user, array $roles, $max = 5)
    {
        return $this->_em
            ->createQuery('
                SELECT DISTINCT w AS workspace, MAX(wr.entryDate) AS max_date
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.roles r
                INNER JOIN Claroline\\CoreBundle\\Entity\\Workspace\\WorkspaceRecent wr WITH wr.workspace = w
                AND wr.user = :usr
                AND r.name IN (:roles)
                GROUP BY w.id
                ORDER BY max_date DESC
            ')
            ->setMaxResults($max)
            ->setParameter('usr', $user)
            ->setParameter('roles', $roles)
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Returns the name, code and number of resources of each workspace.
     *
     * @param int $max
     *
     * @return array
     */
    public function findWorkspacesWithMostResources($max, $organizations = null)
    {
        $qb = $this
            ->createQueryBuilder('ws')
            ->select('ws.name, ws.code, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\ResourceNode', 'rs', 'WITH', 'ws = rs.workspace')
            ->groupBy('ws.id')
            ->orderBy('total', 'DESC');

        if (null !== $organizations) {
            $qb->leftJoin('ws.organizations', 'orgas')
                ->andWhere('orgas IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

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
            SELECT {$select} FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
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
     * Returns the workspaces which are marked as displayable.
     * used by claro_all_workspaces_list_pager_for_resource_rights.
     *
     * @return Workspace[]
     */
    public function findDisplayableWorkspaces()
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible for each user
     * and where name or code contains $search param.
     *
     * @param string $search
     *
     * @return Workspace[]
     */
    public function findDisplayableWorkspacesBySearch($search)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
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

    /**
     *  Used By claro_workspace_update_favourite.
     */
    public function findWorkspaceByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $orderedToolType = 0
    ) {
        if (count($roles) > 0) {
            $dql = '
                SELECT DISTINCT w
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.orderedTools ot
                JOIN ot.rights otr
                JOIN otr.role r
                WHERE w = :workspace
                AND ot.type = :type
                AND r.name IN (:roles)
                AND BIT_AND(otr.mask, :openValue) = :openValue
            ';

            $query = $this->_em->createQuery($dql);
            $query->setParameter('workspace', $workspace);
            $query->setParameter('roles', $roles);
            $query->setParameter('openValue', ToolMaskDecoder::$defaultValues['open']);
            $query->setParameter('type', $orderedToolType);

            return $query->getOneOrNullResult();
        }

        return null;
    }

    public function findByName(
        $search,
        $executeQuery = true,
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $upperSearch = strtoupper($search);
        $upperSearch = trim($upperSearch);
        $upperSearch = preg_replace('/\s+/', ' ', $upperSearch);
        $dql = "
            SELECT w
            FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
            WHERE w.name LIKE :search
            OR UPPER(w.code) LIKE :search
            ORDER BY w.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Eventually used by the message bundle.
     */
    public function findWorkspacesByManager(User $user, $executeQuery = true)
    {
        $roles = $user->getRoles();
        $managerRoles = [];

        foreach ($roles as $role) {
            if (strpos('_'.$role, 'ROLE_WS_MANAGER')) {
                $managerRoles[] = $role;
            }
        }

        $dql = '
            SELECT w
            FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
            JOIN w.roles r
            WHERE r.name IN (:roleNames)

        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $managerRoles);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findWorkspacesByCode(array $codes)
    {
        $dql = '
            SELECT w
            FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
            WHERE w.code IN (:codes)
            ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('codes', $codes);

        return $query->getResult();
    }

    /**
     * Returns all non-personal workspaces.
     *
     * @param string $orderedBy
     * @param string $order
     * @param User   $user
     *
     * @return Workspace[]
     */
    public function findAllNonPersonalWorkspaces(
        $orderedBy = 'name',
        $order = 'ASC',
        User $user = null
    ) {
        $isAdmin = $user ? $user->hasRole('ROLE_ADMIN') : false;

        $qb = $this->createQueryBuilder('w')
          ->select('w')
          ->join('w.organizations', 'o')
          ->leftJoin('o.administrators', 'a')
          ->where('NOT EXISTS (
              SELECT u
              FROM Claroline\CoreBundle\Entity\User u
              JOIN u.personalWorkspace pw
              WHERE pw = w
          )');

        if (!$isAdmin) {
            $qb->andWhere('a.id = ?1')->setParameter(1, $user->getId());
        }

        $qb->orderBy("w.{$orderedBy}", $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all non-personal workspaces which name or code contains $search param.
     * used by the old user picker (12.x).
     *
     * @param string $search
     * @param string $orderedBy
     * @param string $order
     * @param User   $user
     *
     * @return Workspace[]
     */
    public function findAllNonPersonalWorkspacesBySearch(
        $search,
        $orderedBy = 'name',
        $order = 'ASC',
        User $user = null
    ) {
        $isAdmin = $user ? $user->hasRole('ROLE_ADMIN') : false;

        $qb = $this->createQueryBuilder('w');
        $qb->select('w')
          ->join('w.organizations', 'o')
          ->leftJoin('o.administrators', 'a')
          ->where('NOT EXISTS (
              SELECT u
              FROM Claroline\CoreBundle\Entity\User u
              JOIN u.personalWorkspace pw
              WHERE pw = w
          )')
          ->andWhere($qb->expr()->orX(
            $qb->expr()->like('UPPER(w.name)', '?1'),
            $qb->expr()->like('UPPER(w.code)', '?1')
          ))
          ->setParameter(1, "%{$search}%");

        if (!$isAdmin) {
            $qb->andWhere('a.id = ?2')->setParameter(2, $user->getId());
        }

        $qb->orderBy("w.{$orderedBy}", $order);

        return $qb->getQuery()->getResult();
    }

    public function findWorkspaceByCode($workspaceCode, $executeQuery = true)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.code = :workspaceCode
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceCode', $workspaceCode);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findWorkspaceCodesWithPrefix($prefix, $executeQuery = true)
    {
        $dql = '
            SELECT UPPER(w.code) AS code
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE UPPER(w.code) LIKE :search
        ';

        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($prefix);
        $query->setParameter('search', "{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findPersonalWorkspaceExcludingRoles(array $roles, $includeOrphans = false, $empty = false, $offset = null, $limit = null)
    {
        $dql = '
            SELECT w from Claroline\CoreBundle\Entity\Workspace\Workspace w
        ';

        if ($empty) {
            $dql .= '
                LEFT JOIN w.resources r
            ';
        }

        $dql .= '
            LEFT JOIN w.personalUser u
            WHERE u NOT IN (
                SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
                LEFT JOIN u2.roles ur
                LEFT JOIN u2.groups g
                LEFT JOIN g.roles gr
                WHERE (gr IN (:roles) OR ur IN (:roles))

            )
            AND w.personal = true
        ';

        if (!$includeOrphans) {
            $dql .= ' AND u.isRemoved = false';
        }

        if ($empty) {
            $dql .= '
            GROUP BY w.id
            HAVING COUNT(r) <= 2';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        if ($offset) {
            $query->setFirstResult($offset);
        }
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    public function findPersonalWorkspaceByRolesIncludingGroups(array $roles, $includeOrphans = false, $empty = false, $offset = null, $limit = null)
    {
        $dql = '
            SELECT w from Claroline\CoreBundle\Entity\Workspace\Workspace w
            LEFT JOIN w.personalUser u
            JOIN u.roles ur
            LEFT JOIN u.groups g
            LEFT JOIN g.roles gr
            LEFT JOIN gr.workspace grws
            LEFT JOIN ur.workspace uws
        ';

        if ($empty) {
            $dql .= '
                LEFT JOIN w.resources r
            ';
        }

        $dql .= '
            WHERE (uws.id = :wsId
            OR grws.id = :wsId)
            AND u.isRemoved = :isRemoved
            AND w.personal = true
        ';

        if (!$includeOrphans) {
            $dql .= ' AND u.isRemoved = false';
        }

        if ($empty) {
            $dql .= '
            GROUP BY w.id
            HAVING COUNT(r) <= 2';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setMaxResults($limit);
        if ($offset) {
            $query->setFirstResult($offset);
        }

        return $query->getResult();
    }

    public function findNonPersonalByCodeAndName($code, $name, $offset = null, $limit = null)
    {
        $dql = '
            SELECT w FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.personal = false
        ';

        if ($code) {
            $dql .= ' AND w.code LIKE :code';
        }

        if ($name) {
            $dql .= ' AND w.name LIKE :name';
        }

        $code = addcslashes($code, '%_');
        $name = addcslashes($name, '%_');
        $query = $this->_em->createQuery($dql);

        if ($code) {
            $query->setParameter('code', "%{$code}%");
        }

        if ($name) {
            $query->setParameter('name', "%{$name}%");
        }

        $query->setMaxResults($limit);

        if ($offset) {
            $query->setFirstResult($offset);
        }

        return $query->getResult();
    }

    //replace by the finders (it already has page & offset)
    public function findAllPaginated($offset = null, $limit = null)
    {
        $qb = $this
            ->createQueryBuilder('w')
            ->orderBy('w.id')
            ->setMaxResults($limit);
        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}
