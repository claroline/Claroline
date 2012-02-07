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
        $roles = $this->getOwnedRoles(true);
        
        if (! $roles->contains($role))
        {
            $this->roles->add($role);
        }
    }

    /**
     * Removes a role from the subject role collection. The children of the role to be 
     * removed are removed as well, but its parent is kept (ex: given a hierarchy 
     * ROLE_A -> ROLE_B -> ROLE_C, removing ROLE_B from a subject who has ROLE_C will 
     * remove ROLE_B and ROLE_C, but not ROLE_A).
     * 
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        foreach ($this->roles as $storedRole)
        {
            if ($role === $storedRole)
            {
                // remove role
                $this->roles->removeElement($storedRole);
                
                // but keep parent role, if any 
                if (null !== $parentRole = $storedRole->getParent())
                {
                    $this->roles->add($parentRole);
                }
                
                return;
            }
            else
            {
                $currentRole = $storedRole;
                
                while (null !== $parentRole = $currentRole->getParent())
                {
                    if ($parentRole === $role)
                    {
                        // remove children role
                        $this->roles->removeElement($storedRole);
                        
                        // but keep parent role, if any
                        if (null !== $ancestorRole = $parentRole->getParent())
                        {
                            $this->roles->add($ancestorRole);
                        }
                        
                    }
                    
                    $currentRole = $parentRole;
                }
            }
        }
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
    public function getOwnedRoles($includeAncestorRoles = false)
    {
        if (false === $includeAncestorRoles)
        {
            return $this->roles;          
        }
        
        $roles = new ArrayCollection();
        
        foreach ($this->roles as $role)
        {
            $roles->add($role);
                
            while (null !== $parentRole = $role->getParent())
            {
                $roles->add($parentRole);
                $role = $parentRole;
            }
        }
        
        return $roles;
    }
    
    /**
     * Returns the subject's workspace roles as an ArrayCollection of WorkspaceRole objects.
     * 
     * @return ArrayCollection[WorkspaceRole]
     *
    public function getWorkspaceRoleCollection()
    {
        return $this->workspaceRoles;
    }*/
}