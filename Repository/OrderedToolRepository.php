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
use Claroline\CoreBundle\Entity\User;
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
        if (count($roles) === 0) {

            return array();
        } else {
            $dql = '
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.rights r
                JOIN r.role rr
                WHERE ot.workspace = :workspace
                AND rr.name IN (:roleNames)
                AND BIT_AND(r.mask, 1) = 1
                ORDER BY ot.order
            ';
            $query = $this->_em->createQuery($dql);
            $query->setParameter('workspace', $workspace);
            $query->setParameter('roleNames', $roles);

            return $query->getResult();
        }
    }

    public function incWorkspaceOrderedToolOrderForRange(
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

    public function decWorkspaceOrderedToolOrderForRange(
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

    public function incDesktopOrderedToolOrderForRange(
        User $user,
        $fromOrder,
        $toOrder,
        $executeQuery = true
    )
    {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order + 1
            WHERE ot.user = :user
            AND ot.order >= :fromOrder
            AND ot.order < :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);

        return $executeQuery ? $query->execute() : $query;
    }

    public function decDesktopOrderedToolOrderForRange(
        User $user,
        $fromOrder,
        $toOrder,
        $executeQuery = true
    )
    {
        $dql = '
            UPDATE Claroline\CoreBundle\Entity\Tool\OrderedTool ot
            SET ot.order = ot.order - 1
            WHERE ot.user = :user
            AND ot.order > :fromOrder
            AND ot.order <= :toOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('fromOrder', $fromOrder);
        $query->setParameter('toOrder', $toOrder);

        return $executeQuery ? $query->execute() : $query;
    }
}
