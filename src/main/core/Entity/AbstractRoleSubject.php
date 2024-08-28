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
use Doctrine\Common\Collections\Collection;

abstract class AbstractRoleSubject
{
    protected Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * Adds a role to the subject role collection. This method effectively add
     * the role only if it isn't in the collection yet.
     */
    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * Removes a role from the subject role collection.
     */
    public function removeRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * Returns the subject's roles as an ArrayCollection of Role objects.
     */
    public function getEntityRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * Checks if the subject has a given role.
     */
    public function hasRole(Role|string $role): bool
    {
        $roleName = $role instanceof Role ? $role->getName() : $role;
        if (in_array($roleName, $this->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the subject roles as an array of string values.
     */
    public function getRoles(): array
    {
        $roleNames = [];

        foreach ($this->getEntityRoles(true) as $role) {
            $roleNames[] = $role->getName();
        }

        return $roleNames;
    }
}
