<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

//@todo move me to the trait list
abstract class AbstractRoleSubject
{
    protected $roles;
    protected $rolesStringAsArray;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->rolesStringAsArray = [];
    }

    /**
     * Adds a role to the subject role collection. This method effectively add
     * the role only if it isn't in the collection yet.
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
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
     * @return bool
     */
    public function hasRole($roleName)
    {
        if (in_array($roleName, $this->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the subject roles as an array of string values.
     */
    public function getRoles()
    {
        if (is_array($this->rolesStringAsArray) && count($this->rolesStringAsArray) > 0) {
            return $this->rolesStringAsArray;
        }

        $roleNames = [];

        foreach ($this->getEntityRoles(true) as $role) {
            $roleNames[] = $role->getName();
        }

        $this->rolesStringAsArray = $roleNames;

        return $roleNames;
    }
}
