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

use Doctrine\ORM\EntityRepository;

class BBBRepository extends EntityRepository
{
    public function findUsedServers()
    {
        return array_map('current', $this->getEntityManager()
            ->createQuery('
                SELECT b.runningOn
                FROM Claroline\BigBlueButtonBundle\Entity\BBB AS b
                WHERE b.runningOn IS NOT NULL
                GROUP BY b.runningOn
            ')
            ->getScalarResult()
        );
    }
}
