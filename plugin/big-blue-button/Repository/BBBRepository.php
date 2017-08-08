<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class BBBRepository extends EntityRepository
{
    public function findBBBWithDatesByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        $dql = '
            SELECT bbb
            FROM Claroline\BigBlueButtonBundle\Entity\BBB bbb
            JOIN bbb.resourceNode r
            JOIN r.workspace w
            WHERE w = :workspace
            AND bbb.startDate IS NOT NULL
            AND bbb.endDate IS NOT NULL
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }
}
