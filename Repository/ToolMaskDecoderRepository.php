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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Doctrine\ORM\EntityRepository;

class ToolMaskDecoderRepository extends EntityRepository
{
    public function findMaskDecodersByTool(Tool $tool, $executeQuery = true)
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            ORDER BY tmd.value ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tool', $tool);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllMaskDecoders($executeQuery = true)
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            ORDER BY tmd.value ASC
        ';
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findMaskDecodersByToolAndName(
        Tool $tool,
        $name,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT tmd
            FROM Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder tmd
            WHERE tmd.tool = :tool
            AND tmd.name = :name
            ORDER BY tmd.value ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tool', $tool);
        $query->setParameter('name', $name);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
