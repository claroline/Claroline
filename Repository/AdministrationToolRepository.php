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

class AdministrationToolRepository extends EntityRepository
{
    public function findByRoles(array $roles)
    {
        $rolesNames = [];

        foreach ($roles as $role) {
            $rolesNames[] = $role->getRole();
        }

        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Administration\Tool tool
            JOIN tool.roles role
            WHERE role.name IN (:roleNames)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $rolesNames);

        return $query->getResult();
    }
} 