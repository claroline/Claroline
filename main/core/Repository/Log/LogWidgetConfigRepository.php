<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Log;

use Doctrine\ORM\EntityRepository;

class LogWidgetConfigRepository extends EntityRepository
{
    public function findByWorkspaces(array $workspaces)
    {
        $ids = array();

        foreach ($workspaces as $workspace) {
            $ids[] = $workspace->getId();
        }

        $dql = "SELECT lw FROM Claroline\CoreBundle\Entity\Log\LogWidgetConfig lw
            JOIN lw.widgetInstance wi
            JOIN wi.workspace ws
            WHERE ws.id IN (:ids)";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}
