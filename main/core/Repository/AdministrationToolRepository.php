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
        $isAdmin = false;

        foreach ($roles as $role) {
            $rolesNames[] = $role->getRole();

            if ($role->getRole() === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool";
        } else {
            $dql = "
                SELECT tool FROM Claroline\CoreBundle\Entity\Tool\AdminTool tool
                JOIN tool.roles role
                WHERE role.name IN (:roleNames)
            ";
        }

        $query = $this->_em->createQuery($dql);

        if (!$isAdmin) {
            $query->setParameter('roleNames', $rolesNames);
        }

        return $query->getResult();
    }
}
