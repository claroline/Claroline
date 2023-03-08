<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class ScormRepository extends EntityRepository
{
    public function findNbScormWithSameSource($hashName, Workspace $workspace)
    {
        $dql = '
            SELECT COUNT(s.id)
            FROM Claroline\ScormBundle\Entity\Scorm s
            JOIN s.resourceNode r
            JOIN r.workspace w
            WHERE s.hashName = :hashName
            AND w = :workspace
        ';

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('hashName', $hashName);
        $query->setParameter('workspace', $workspace);

        return $query->getSingleScalarResult();
    }
}
