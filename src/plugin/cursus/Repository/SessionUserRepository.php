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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\EntityRepository;

class SessionUserRepository extends EntityRepository
{
    public function findByOrganization(Organization $organization)
    {
        return $this->_em
            ->createQuery('
                SELECT su FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                INNER JOIN su.session s
                INNER JOIN s.course c
                INNER JOIN c.organizations o
                WHERE o IN (:organization)
            ')
            ->setParameter('organization', $organization)
            ->getResult();
    }
}
