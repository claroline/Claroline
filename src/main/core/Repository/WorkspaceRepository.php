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

use Claroline\AppBundle\Repository\UniqueValueFinder;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class WorkspaceRepository extends EntityRepository
{
    use UniqueValueFinder;

    public function search(string $search, int $nbResults): array
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

    public function findByUuidOrSlug(string $identifier): ?Workspace
    {
        return $this->createQueryBuilder('w')
            ->where('w.uuid = :identifier')
            ->orWhere('w.slug = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns the workspaces whose at least one tool is accessible to one of the given roles.
     *
     * @param string[] $roleNames
     *
     * @return Workspace[]
     */
    public function findByRoles(array $roleNames): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT w 
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
                WHERE EXISTS (
                    SELECT ot
                    FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                    JOIN ot.rights otr
                    JOIN otr.role otrr
                    WHERE ot.contextName = :contextName
                      AND ot.contextId = w.uuid
                      AND otrr.name IN (:roles)
                      AND BIT_AND(otr.mask, 1) = 1
                )
            ')
            ->setParameter('contextName', WorkspaceContext::getName())
            ->setParameter('roles', $roleNames)
            ->getResult();
    }

    public function checkAccess(Workspace $workspace, array $roleNames, ?string $toolName = null, ?string $action = 'open'): bool
    {
        $dql = '
            SELECT COUNT(ot)
            FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            LEFT JOIN ot.rights AS r
            LEFT JOIN r.role AS rr
            WHERE ot.contextName = :contextName
            AND ot.contextId = :workspaceId

            AND (ot.public = 1 OR (rr.name IN (:roleNames) AND EXISTS (
                SELECT d
                FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder AS d
                WHERE d.tool = ot.name
                AND d.name = :action
                AND BIT_AND(r.mask, d.value) = d.value
            )))
        ';

        if ($toolName) {
            $dql .= ' AND ot.name = :toolName';
        }

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getUuid());
        $query->setParameter('contextName', WorkspaceContext::getName());
        $query->setParameter('roleNames', $roleNames);
        $query->setParameter('action', strtoupper($action));

        if ($toolName) {
            $query->setParameter('toolName', $toolName);
        }

        return 0 < (int) $query->getSingleScalarResult();
    }

    public function findManaged(string $userId): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace AS w
                WHERE w.personal = 0
                AND EXISTS (
                    SELECT u.id
                    FROM Claroline\CoreBundle\Entity\User AS u
                    LEFT JOIN u.roles AS r
                    LEFT JOIN u.groups AS g
                    LEFT JOIN g.roles AS gr
                    WHERE u.id = :userId
                      AND (r.name LIKE :managerRolePrefix OR gr.name LIKE :managerRolePrefix)
                )
            ')
            ->setParameters([
                'userId' => $userId,
                'managerRolePrefix' => 'ROLE_WS_MANAGER_%',
            ])
            ->getResult();
    }

    public function findWithNoOrganization(): iterable
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT w
                FROM Claroline\CoreBundle\Entity\Workspace\Workspace AS w
                LEFT JOIN w.organizations AS o
                WHERE o IS NULL
            ')
            ->toIterable();
    }
}
