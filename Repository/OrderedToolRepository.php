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

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class OrderedToolRepository extends EntityRepository
{
    /**
     * Returns the workspace ordered tools accessible to some given roles.
     *
     * @param Workspace $workspace
     * @param array             $roles
     *
     * @return array[OrderedTool]
     */
    public function findByWorkspaceAndRoles(Workspace $workspace, array $roles)
    {
        $rolesRestriction = '';
        $first = true;
        $i = 0;

        foreach ($roles as $roleName) {
            if ($first) {
                $first = false;
                $rolesRestriction .= "(r.name like :role{$i}";
            } else {
                $rolesRestriction .= " OR r.name like :role{$i}";
            }
            $i++;
        }

        $rolesRestriction .= ')';
        $dql = "
            SELECT ot FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            JOIN ot.workspace ws
            JOIN ot.roles r
            WHERE ws.id = {$workspace->getId()}
            AND {$rolesRestriction}
            ORDER BY ot.order
        ";
        $query = $this->_em->createQuery($dql);

        $i = 0;
        
        foreach ($roles as $roleName) {
            $query->setParameter("role{$i}", $roleName);
            $i++;
        }

        return $query->getResult();
    }

    public function incOrderedToolOrderForRange(
        Workspace $workspace,
        $fromOrder,
        $toOrder,
        $executeQuery = true
    )
    {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order + 1
            WHERE ot.workspace = :workspace
            AND ot.order >= :fromOrder
            AND ot.order < :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);

        return $executeQuery ? $query->execute() : $query;
    }

    public function decOrderedToolOrderForRange(
        Workspace $workspace,
        $fromOrder,
        $toOrder,
        $executeQuery = true
    )
    {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order - 1
            WHERE ot.workspace = :workspace
            AND ot.order > :fromOrder
            AND ot.order <= :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);

        return $executeQuery ? $query->execute() : $query;
    }
}
