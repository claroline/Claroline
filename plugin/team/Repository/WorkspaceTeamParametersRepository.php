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

class WorkspaceTeamParametersRepository extends EntityRepository
{
    public function findParametersByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    ) {
        $dql = "
            SELECT wtp
            FROM Claroline\TeamBundle\Entity\WorkspaceTeamParameters wtp
            WHERE wtp.workspace = :workspace
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
