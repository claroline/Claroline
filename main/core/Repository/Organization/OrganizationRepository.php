<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Organization;

use Doctrine\ORM\EntityRepository;

class OrganizationRepository extends EntityRepository
{
    public function findOrganizationsByIds(array $ids)
    {
        $dql = '
            SELECT o
            FROM Claroline\CoreBundle\Entity\Organization\Organization o
            WHERE o.id IN (:ids)
            ORDER BY o.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}
