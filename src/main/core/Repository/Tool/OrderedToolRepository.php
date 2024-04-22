<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Tool;

use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\ORM\EntityRepository;

class OrderedToolRepository extends EntityRepository
{
    public function findByContext(string $context, string $contextId = null): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                WHERE ot.contextName = :contextName
                  AND (ot.contextId IS NULL OR ot.contextId = :contextId)
                ORDER BY ot.order
            ')
            ->setParameter('contextName', $context)
            ->setParameter('contextId', $contextId)
            ->getResult();
    }

    public function findOneByNameAndContext(string $name, string $context, string $contextId = null): ?OrderedTool
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                WHERE ot.contextName = :contextName
                  AND (ot.contextId IS NULL OR ot.contextId = :contextId)
                  AND ot.name = :name
            ')
            ->setParameter('name', $name)
            ->setParameter('contextName', $context)
            ->setParameter('contextId', $contextId)
            ->getOneOrNullResult();
    }

    /**
     * @deprecated
     */
    public function countByDesktopAndRoles(array $roles): int
    {
        if (0 === count($roles)) {
            return 0;
        }

        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(ot.id)
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool AS ot
                JOIN ot.rights AS r
                JOIN r.role AS rr
                WHERE ot.contextName = :contextName
                    AND rr.name IN (:roleNames)
                    AND BIT_AND(r.mask, 1) = 1
                    ORDER BY ot.order
            ')
            ->setParameter('roleNames', $roles)
            ->setParameter('contextName', DesktopContext::getName())
            ->getSingleScalarResult();
    }
}
