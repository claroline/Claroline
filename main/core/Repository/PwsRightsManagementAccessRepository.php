<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PwsRightsManagementAccessRepository extends EntityRepository
{
    public function findByRoles($roleNames)
    {
        $dql = "SELECT pwsr FROM Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess pwsr
         JOIN pwsr. role r
         WHERE r.name in (:roleNames)";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roleNames);

        return $query->getResult();
    }
}
