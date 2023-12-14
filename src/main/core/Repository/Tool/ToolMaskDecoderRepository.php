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

use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Doctrine\ORM\EntityRepository;

class ToolMaskDecoderRepository extends EntityRepository
{
    /**
     * @return ToolMaskDecoder[]
     *
     * @deprecated we don't need a custom repo method for this
     */
    public function findMaskDecodersByTool(string $toolName): array
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $toolName);

        return $query->getResult();
    }

    /**
     * @deprecated we don't need a custom repo method for this
     */
    public function findMaskDecoderByToolAndName(string $toolName, string $name): ?ToolMaskDecoder
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            AND tmd.name = :name
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $toolName);
        $query->setParameter('name', $name);

        return $query->getOneOrNullResult();
    }

    public function findCustomMaskDecodersByTool(string $toolName): array
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            AND tmd.name NOT IN (:defaultActions)
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $toolName);
        $query->setParameter('defaultActions', ToolMaskDecoder::DEFAULT_ACTIONS);

        return $query->getResult();
    }
}
