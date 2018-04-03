<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Repository;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class PlannedNotificationRepository extends EntityRepository
{
    public function findByAction(Workspace $workspace, $action)
    {
        $dql = '
            SELECT pn
            FROM Claroline\PlannedNotificationBundle\Entity\PlannedNotification pn
            JOIN pn.workspace w
            WHERE w = :workspace
            AND pn.action = :action
            AND pn.roles IS EMPTY
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('action', $action);

        return $query->getResult();
    }

    public function findByActionAndRole(Workspace $workspace, $action, Role $role)
    {
        $dql = '
            SELECT pn
            FROM Claroline\PlannedNotificationBundle\Entity\PlannedNotification pn
            JOIN pn.workspace w
            LEFT JOIN pn.roles r
            WHERE w = :workspace
            AND pn.action = :action
            AND (
              pn.roles IS EMPTY
              OR r = :role
          )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('action', $action);
        $query->setParameter('role', $role);

        return $query->getResult();
    }
}
