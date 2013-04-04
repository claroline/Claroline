<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Role;

abstract class AbstractRoleSubject
{
    protected $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * Adds a role to the subject role collection. This method effectively add
     * the role only if it isn't in the collection yet, and if it isn't the ancestor
     * of an already stored role (ex: given a hierarchy ROLE_A -> ROLE_B, adding the
     * role ROLE_A to a subject who already has the role ROLE_B won't have any effect).
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $roles = $this->getOwnedRoles();

        if (!$roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * Removes a role from the subject role collection.
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Returns the subject's roles as an ArrayCollection of Role objects.
     *
     * By default, this method will only return the actual stored roles,
     * which are always the leaf nodes of a hierarchy, if any. For example,
     * given a hierarchy :
     *
     *  ROLE_A
     *      ROLE_B
     *          ROLE_C,
     *
     * if the current subject has ROLE_C, the returned collection will only
     * include ROLE_C. But if the first parameter is set to true, the collection
     * will also contain the ancestors of ROLE_C, i.e. ROLE_B and ROLE_A.
     *
     * @param boolean $includeAncestorRoles
     *
     * @return ArrayCollection[Role]
     */
    public function getOwnedRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if the subject has a given role. This method will explore
     * role hierarchies if necessary.
     *
     * @param string $roleName
     *
     * @return boolean
     */
    public function hasRole($roleName)
    {
        if (in_array($roleName, $this->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the subject roles as an array of sting values
     */
    public function getRoles()
    {
        $roleNames = array();

        foreach ($this->getOwnedRoles(true) as $role) {
            $roleNames[] = $role->getName();
        }

        return $roleNames;
    }
}