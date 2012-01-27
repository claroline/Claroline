<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\WorkspaceRole;

// TODO: Implements AdvancedUserInterface

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\Table(name="claro_user")
 * @DoctrineAssert\UniqueEntity("username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @ORM\Column(name="username", type="string", length="255", unique="true")
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length="255")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length="255")
     */
    protected $salt;

    /**
     * @Assert\NotBlank()
     */
    protected $plainPassword;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Role", 
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_user_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\WorkspaceRole", 
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_user_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $workspaceRoles;
    
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->workspaceRoles = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }
    
    /**
     * Checks if the user has a given role. This method will explore
     * role hierarchies if necessary.
     * 
     * @param string $roleName
     * 
     * @return boolean
     */
    public function hasRole($roleName)
    {
        foreach ($this->roles as $role)
        {
            if ($role->getName() == $roleName)
            {
                return true;
            }
            else
            {
                while (null !== $parentRole = $role->getParent())
                {
                    if ($parentRole->getName() == $roleName)
                    {
                        return true;
                    }
                    
                    $role = $parentRole;
                }
            }
        }

        return false;
    }
    
    /**
     * Returns the user's roles (including role's ancestors) as an array 
     * of string values (needed for Symfony security checks).
     * 
     * @return array[string]
     */
    public function getRoles()
    {
        $roleNames = array();
        
        foreach ($this->getRoleCollection(true) as $role)
        {
            $roleNames[] = $role->getName();
        }
        
        return $roleNames;
    }
    
    /**
     * Returns the user's roles as an ArrayCollection of Role objects.
     * 
     * By default, this method will only return the actual stored roles, 
     * which are always the leaf nodes of a hierarchy, if any. For example,
     * given a hierarchy :
     * 
     *  ROLE_A
     *      ROLE_B
     *          ROLE_C,
     * 
     * if the current user has ROLE_C, the returned collection will only 
     * include ROLE_C. But if the first parameter is set to true, the collection
     * will also contain the ancestors of ROLE_C, i.e. ROLE_B and ROLE_A.
     * 
     * @param boolean $includeAncestorRoles
     * 
     * @return ArrayCollection[Role]
     */
    public function getRoleCollection($includeAncestorRoles = false)
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
     * Returns the user's workspace roles as an ArrayCollection of WorkspaceRole objects.
     * 
     * @return ArrayCollection[WorkspaceRole]
     */
    public function getWorkspaceRoleCollection()
    {
        return $this->workspaceRoles;
    }
    
    /**
     * Adds a role to the user role collection. This method effectively add
     * the role only if it isn't in the collection yet, and if it isn't the ancestor
     * of an already stored role (ex: given a hierarchy ROLE_USER -> ROLE_ADMIN, 
     * adding the role ROLE_USER to an user who already has the role ROLE_ADMIN 
     * won't have any effect).
     * 
     * @param Role $role 
     */
    public function addRole(Role $role)
    {
        if (! $this->hasRole($role->getName()))
        {
            $this->roles->add($role);
        }
    }

    /**
     * Removes a role from the user role collection. The children of the role to be 
     * removed are removed as well, but its parent is kept (ex: given a hierarchy 
     * ROLE_A -> ROLE_B -> ROLE_C, removing ROLE_B from an user who has ROLE_C will 
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

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function equals(UserInterface $user)
    {
        if (! $user instanceof User) 
        {
            return false;
        }

        if ($this->firstName !== $user->getFirstName()) 
        {
            return false;
        }

        if ($this->lastName !== $user->getLastName()) 
        {
            return false;
        }

        if ($this->username !== $user->getUsername()) 
        {
            return false;
        }

        if ($this->password !== $user->getPassword()) 
        {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) 
        {
            return false;
        }
    }
}