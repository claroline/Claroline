<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class TeamRepository extends EntityRepository
{
    public function findTeamsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            WHERE t.workspace = :workspace
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findTeamsByWorkspaceAndName(
        Workspace $workspace,
        $teamName,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT t
            FROM Claroline\TeamBundle\Entity\Team t
            WHERE t.workspace = :workspace
            AND t.name = :teamName
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('teamName', $teamName);

        return $executeQuery ? $query->getResult() : $query;
    }
}
