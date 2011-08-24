<?php

namespace Claroline\SecurityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\SecurityBundle\Entity\Role;

class RoleRepository extends EntityRepository
{
    /* Not needed actually. See RoleManager. */

    public function create(Role $role)
    {
        if (! $this->isUnique($role->getName()))
        {
            throw new \Exception("There's already a registered role named '{$role->getName()}'.");
        }
        
        $this->_em->persist($role);
        $this->_em->flush();
    }

    public function isUnique($roleName)
    {
        $roles = $this->findByName($roleName);

        if (count($roles) == 0)
        {
            return true;
        }

        return false;
    }
}