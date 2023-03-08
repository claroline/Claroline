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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Doctrine\ORM\EntityRepository;

class ToolMaskDecoderRepository extends EntityRepository
{
    /**
     * @return ToolMaskDecoder[]
     */
    public function findMaskDecodersByTool(Tool $tool)
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $tool);

        return $query->getResult();
    }

    /**
     * @param string $name
     *
     * @return ToolMaskDecoder|null
     */
    public function findMaskDecoderByToolAndName(Tool $tool, $name)
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            AND tmd.name = :name
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $tool);
        $query->setParameter('name', $name);

        return $query->getOneOrNullResult();
    }

    public function findCustomMaskDecodersByTool(Tool $tool)
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            AND tmd.name NOT IN (:defaultActions)
            ORDER BY tmd.value ASC
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tool', $tool);
        $query->setParameter('defaultActions', ToolMaskDecoder::$defaultActions);

        return $query->getResult();
    }
}
