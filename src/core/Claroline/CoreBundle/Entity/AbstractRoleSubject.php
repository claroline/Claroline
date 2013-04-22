<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Role;

abstract class AbstractRoleSubject
{
    protected $roles;
    protected $rolesStringAsArray;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->rolesStringAsArray = array();
    }

    /**
     * Adds a role to the subject role collection. This method effectively add
     * the role only if it isn't in the collection yet.
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $roles = $this->getEntityRoles();

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
     * @param boolean $includeAncestorRoles
     *
     * @return ArrayCollection[Role]
     */
    public function getEntityRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if the subject has a given role.
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
        if (count($this->rolesStringAsArray) > 0) {
            return $this->rolesStringAsArray;
        }

        $roleNames = array();

        foreach ($this->getEntityRoles(true) as $role) {
            $roleNames[] = $role->getName();
        }

        return $roleNames;
    }
}