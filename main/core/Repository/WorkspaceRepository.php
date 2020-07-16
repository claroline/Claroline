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

class WorkspaceRepository extends EntityRepository
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('w')
            ->where('(UPPER(w.name) LIKE :search OR UPPER(w.code) LIKE :search)')
            ->andWhere('w.displayable = true')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }

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
     * @param array $organizations
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

        if (!empty($organizations)) {
            $qb->join('w.organizations', 'orgas')
                ->andWhere('orgas IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
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
     *
     * @return int[]
     */
    public function findOpenWorkspaceIds(
        array $roleNames,
        array $workspaces,
        $toolName = null,
        $action = 'open'
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

        if ($toolName) {
            $query->setParameter('toolName', $toolName);
        }

        return $query->getResult();
    }

    /**
     * Returns the name, code and number of resources of each workspace.
     *
     * @param int   $max
     * @param array $organizations
     *
     * @return array
     */
    public function findWorkspacesWithMostResources($max, array $organizations = [])
    {
        $qb = $this
            ->createQueryBuilder('ws')
            ->select('ws.name, ws.code, COUNT(rs.id) AS total')
            ->leftJoin('Claroline\CoreBundle\Entity\Resource\ResourceNode', 'rs', 'WITH', 'ws = rs.workspace')
            ->groupBy('ws.id')
            ->orderBy('total', 'DESC');

        if (!empty($organizations)) {
            $qb
                ->leftJoin('ws.organizations', 'o')
                ->andWhere('o IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        if ($max > 1) {
            $qb->setMaxResults($max);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCodes(array $codes)
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

    /**
     * Returns the list of workspace codes starting with $prefix.
     * Useful to auto generate unique workspace codes.
     *
     * @param string $prefix
     *
     * @return string[]
     */
    public function findWorkspaceCodesWithPrefix($prefix)
    {
        return array_map(function (array $ws) { return $ws['code']; }, $this->_em->createQuery('
                SELECT UPPER(w.code) AS code
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE UPPER(w.code) LIKE :search
            ')
            ->setParameter('search', strtoupper($prefix).'%')
            ->getResult()
        );
    }
}
