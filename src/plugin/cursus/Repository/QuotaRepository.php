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

use Doctrine\ORM\EntityRepository;

class QuotaRepository extends EntityRepository
{
    public function findOneByOrganizations(array $organizations)
    {
        return $this->_em->createQuery('
            SELECT q FROM Claroline\CursusBundle\Entity\Quota q
            INNER JOIN q.organization o
            WHERE o IN (:organizations)
        ')
        ->setParameter('organizations', $organizations)
        ->getSingleResult();
    }
}
