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
     * Returns the workspaces which are not a user's personal workspace.
     *
     * @return Workspace[]
     */
    public function findNonPersonal()
    {
        return $this->_em
            ->createQuery('
                SELECT w FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE w.id NOT IN (
                    SELECT pws.id FROM Claroline\CoreBundle\Entity\User user
                    JOIN user.personalWorkspace pws
                )
                ORDER BY w.id
            ')
            ->getResult();
    }

    /**
     * Returns the workspaces which are not a user's personal workspace.
     *
     * @return Workspace[]
     */
    public function findNonPersonalWorkspaces()
    {
        return $this->_em
            ->createQuery('
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE w.isPersonal = false
                ORDER BY w.id
            ')
            ->getResult();
    }

    /**
     * Returns the workspaces whose at least one tool is accessible to anonymous users.
     *
     * @param int $orderedToolType
     *
     * @return Workspace[]
     */
    public function findByAnonymous($orderedToolType = 0)
    {
        return $this->_em
            ->createQuery("
                SELECT DISTINCT w
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.orderedTools ot
                JOIN ot.rights otr
                JOIN otr.role r
                WHERE r.name = 'ROLE_ANONYMOUS'
                AND ot.type = :type
                AND BIT_AND(otr.mask, :openValue) = :openValue
            ")
            ->setParameter('openValue', ToolMaskDecoder::$defaultValues['open'])
            ->setParameter('type', $orderedToolType)
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
                WHERE w.isPersonal = true
            ')
            ->getSingleScalarResult();
    }

    /**
     * Counts the non personal workspaces.
     *
     * @return int
     */
    public function countNonPersonalWorkspaces()
    {
        return $this->_em
            ->createQuery('
                SELECT COUNT(w)
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE w.isPersonal = false
            ')
            ->getSingleScalarResult();
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
     * Returns the workspaces whose at least one tool is accessible to one of the given roles.
     *
     * @param string   $search
     * @param string[] $roles
     *
     * @return array[Workspace]
     */
    public function findBySearchAndRoles($search, array $roles)
    {
        return $this->_em
            ->createQuery('
                SELECT DISTINCT w FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.roles r
                WHERE r.name in (:roles)
                AND (
                    UPPER(w.name) LIKE :search
                    OR UPPER(w.code) LIKE :search
                )
                ORDER BY w.name
            ')
            ->setParameter('roles', $roles)
            ->setParameter('search', '%'.strtoupper($search).'%')
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
     * Returns the workspaces whose at least one tool is accessible to one of the given roles.
     *
     * @param string[] $roleNames
     * @param int      $orderedToolType
     *
     * @return Workspace[]
     */
    public function findByRoleNames(array $roleNames, $orderedToolType = 0)
    {
        return $this->_em
            ->createQuery('
                SELECT DISTINCT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                JOIN w.orderedTools ot
                JOIN ot.rights otr
                JOIN otr.role r
                WHERE r.name IN (:roleNames)
                AND ot.type = :type
                AND BIT_AND(otr.mask, :openValue) = :openValue
                ORDER BY w.name
            ')
            ->setParameter('roleNames', $roleNames)
            ->setParameter('openValue', ToolMaskDecoder::$defaultValues['open'])
            ->setParameter('type', $orderedToolType)
            ->getResult();
    }

    /**
     * Returns the ids of the workspaces a user is member of, filtered by a set of roles
     * the user must have in those workspaces. Role names are actually prefixes of the
     * target role (e.g. 'ROLE_WS_COLLABORATOR' instead of 'ROLE_WS_COLLABORATOR_123').
     *
     * @param User     $user
     * @param string[] $roleNames
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
        if ($restrictionIds === null || count($restrictionIds) === 0) {
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
            ->createQuery("
                SELECT DISTINCT w AS workspace, MAX(l.dateLog) AS max_date
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                JOIN w.roles r
                INNER JOIN Claroline\\CoreBundle\\Entity\\Log\\Log l WITH l.workspace = w
                JOIN l.doer u
                WHERE l.action = 'workspace-tool-read'
                AND u.id = :userId
                AND r.name IN (:roles)
                GROUP BY w.id
                ORDER BY max_date DESC
            ")
            ->setMaxResults($max)
            ->setParameter('userId', $user->getId())
            ->setParameter('roles', $roles)
            ->getResult();
    }

    /**
     * Returns the name, code and number of resources of each workspace.
     *
     * @param int $max
     *
     * @return array
     */
    public function findWorkspacesWithMostResources($max)
    {
        $qb = $this
            ->createQueryBuilder('ws')
            ->select('ws.name, ws.code, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\ResourceNode', 'rs', 'WITH', 'ws = rs.workspace')
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
     * Returns the workspaces which are visible for an authenticated user and allow
     * self-registration (user's workspaces are excluded).
     *
     * @param User $user
     *
     * @return Workspace[]
     */
    public function findWorkspacesWithSelfRegistration(User $user)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND w.selfRegistration = true
            AND w.id NOT IN (
                SELECT w2.id FROM Claroline\CoreBundle\Entity\Workspace\Workspace w2
                JOIN w2.roles r
                JOIN r.users u
                WHERE u.id = :userId
            )
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible for an authenticated user and allow
     * self-registration (user's workspaces are excluded).
     *
     * @param User   $user
     * @param string $search
     *
     * @return Workspace[]
     */
    public function findWorkspacesWithSelfRegistrationBySearch(User $user, $search)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND w.selfRegistration = true
            AND (
                UPPER(w.name) LIKE :search
                OR UPPER(w.code) LIKE :search
            )
            AND w.id NOT IN (
                SELECT w2.id FROM Claroline\CoreBundle\Entity\Workspace\Workspace w2
                JOIN w2.roles r
                JOIN r.users u
                WHERE u.id = :userId
            )
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $search = strtoupper($search);
        $query->setParameter('search', "%{$search}%");

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

    public function findWorkspacesWithSelfUnregistrationByRoles(array $roles)
    {
        $dql = '
            SELECT DISTINCT w FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
            JOIN w.roles r
            WHERE w.selfUnregistration = true
            AND r.name IN (:roles)
            ORDER BY w.name
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible and are not in the given list.
     *
     * @param array $excludedWorkspaces
     *
     * @return Workspace[]
     */
    public function findDisplayableWorkspacesWithout(array $excludedWorkspaces)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND w NOT IN (:excludedWorkspaces)
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('excludedWorkspaces', $excludedWorkspaces);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are visible, are not in the given list
     * and whose name or code contains $search param.
     *
     * @param array  $excludedWorkspaces
     * @param string $search
     *
     * @return Workspace[]
     */
    public function findDisplayableWorkspacesWithoutBySearch(
        array $excludedWorkspaces,
        $search
    ) {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND (
                UPPER(w.name) LIKE :search
                OR UPPER(w.code) LIKE :search
            )
            AND w NOT IN (:excludedWorkspaces)
            ORDER BY w.name
        ';
        $upperSearch = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");
        $query->setParameter('excludedWorkspaces', $excludedWorkspaces);

        return $query->getResult();
    }

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

    public function countUsers($workspaceId)
    {
        $dql = '
            SELECT count(w) FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            JOIN w.roles r
            JOIN r.users u
            WHERE w.id = :workspaceId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspaceId);

        return $query->getSingleScalarResult();
    }

    /**
     * Returns the workspaces which are marked as displayable and are not someone's
     * personal workspace.
     *
     * @return Workspace[]
     */
    public function findDisplayableNonPersonalWorkspaces()
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND NOT EXISTS (
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                JOIN u.personalWorkspace pw
                WHERE pw = w
            )
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are marked as displayable and are not someone's
     * personal workspace and where name or code contains $search param.
     *
     * @param string $search
     *
     * @return Workspace[]
     */
    public function findDisplayableNonPersonalWorkspacesBySearch($search)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND (
                UPPER(w.name) LIKE :search
                OR UPPER(w.code) LIKE :search
            )
            AND NOT EXISTS (
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                JOIN u.personalWorkspace pw
                WHERE pw = w
            )
            ORDER BY w.name
        ';

        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are marked as displayable and are someone's
     * personal workspace.
     *
     * @return Workspace[]
     */
    public function findDisplayablePersonalWorkspaces()
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND EXISTS (
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                JOIN u.personalWorkspace pw
                WHERE pw = w
            )
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the workspaces which are marked as displayable and are someone's
     * personal workspace and where name or code contains $search param.
     *
     * @param string $search
     *
     * @return Workspace[]
     */
    public function findDisplayablePersonalWorkspacesBySearch($search)
    {
        $dql = '
            SELECT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.displayable = true
            AND (
                UPPER(w.name) LIKE :search
                OR UPPER(w.code) LIKE :search
            )
            AND EXISTS (
                SELECT u
                FROM Claroline\CoreBundle\Entity\User u
                JOIN u.personalWorkspace pw
                WHERE pw = w
            )
            ORDER BY w.name
        ';

        $search = strtoupper($search);
        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

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
            AND w.isPersonal = true
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
            AND w.isPersonal = true
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
            WHERE w.isPersonal = false
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
}
