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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class WorkspaceRepository extends EntityRepository
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('w')
            ->where('(UPPER(w.name) LIKE :search OR UPPER(w.code) LIKE :search)')
            ->andWhere('w.hidden = false')
            ->andWhere('w.archived = false')
            ->andWhere('w.personal = false')
            ->andWhere('w.model = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
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
     * @param string[] $roleNames
     *
     * @return Workspace[]
     */
    public function findByRoles(array $roleNames)
    {
        return $this->_em
            ->createQuery('
                SELECT DISTINCT w 
                FROM Claroline\\CoreBundle\\Entity\\Workspace\\Workspace w
                WHERE EXISTS (
                    SELECT ot
                    FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                    JOIN ot.rights otr
                    JOIN otr.role otrr
                    WHERE ot.workspace = w
                    AND otrr.name in (:roles)
                    AND BIT_AND(otr.mask, 1) = 1
                )

            ')
            ->setParameter('roles', $roleNames)
            ->getResult();
    }

    public function checkAccess(Workspace $workspace, array $roleNames, ?string $toolName = null, ?string $action = 'open')
    {
        $dql = '
            SELECT COUNT(ot)
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.workspace w
            JOIN ot.tool t
            JOIN ot.rights r
            JOIN r.role rr
            WHERE w.id = :workspaceId
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
            $dql .= ' AND t.name = :toolName';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('roleNames', $roleNames);
        $query->setParameter('action', $action);

        if ($toolName) {
            $query->setParameter('toolName', $toolName);
        }

        return 0 < (int) $query->getSingleScalarResult();
    }

    /**
     * Returns the name, code and number of resources of each workspace.
     *
     * @param int $max
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
     * Returns the list of workspace codes starting with $prefix.
     * Useful to auto generate unique workspace codes.
     */
    public function findWorkspaceCodesWithPrefix(string $prefix): array
    {
        return array_map(
            function (array $ws) {
                return $ws['code'];
            },
            $this->_em->createQuery('
                SELECT UPPER(w.code) AS code
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE UPPER(w.code) LIKE :search
            ')
            ->setParameter('search', strtoupper($prefix).'%')
            ->getResult()
        );
    }
}
