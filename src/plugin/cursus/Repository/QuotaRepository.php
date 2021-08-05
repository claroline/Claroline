<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CursusBundle\Entity\Quota;
use Doctrine\ORM\EntityRepository;

class QuotaRepository extends EntityRepository
{
    // TEST
    public function countValidated(Quota $quota)
    {
        return $this->_em->createQuery('
            SELECT COUNT(DISTINCT su) FROM Claroline\CursusBundle\Entity\Registration\SessionUser su
            INNER JOIN su.session s
            INNER JOIN s.course c
            INNER JOIN c.organizations o
            WHERE c.usedByQuotas = 1
                AND su.type = \'learner\'
                AND su.confirmed = 1
                AND su.validated = 1
                AND o IN (:organization)
        ')
        ->setParameters([
            'organization' => $quota->getOrganization()
        ])
        ->getSingleScalarResult();
    }
}
