<?php

namespace Claroline\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\SecurityBundle\Entity\Role;

// TODO: Implements AdvancedUserInterface

/**
 * @ORM\Entity(repositoryClass="Claroline\UserBundle\Repository\UserRepository")
 * @ORM\Table
 * (
 *     name="claro_user",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="username_idx", columns={"username"})}
 * )
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
     * @ORM\ManyToMany(targetEntity="Claroline\SecurityBundle\Entity\Role", cascade={"persist"})
     * @ORM\JoinTable(name="claro_user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(
     *  targetEntity="Claroline\WorkspaceBundle\Entity\Workspace", 
     *  inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_workspace_user")
     */
    protected $workspaces;
    
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
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
     * Returns the user's roles string values (needed for Symfony security checks).
     * 
     * @return array
     */
    public function getRoles()
    {
        $roleNames = array();
        
        foreach ($this->roles->toArray() as $role)
        {
            $roleNames[] = $role->getName();
        }
        
        return $roleNames;
    }
    
    /**
     * Returns the user's role object representations.
     * 
     * @return array[Role]
     */
    public function getRoleObjects()
    {
        return $this->roles->toArray();
    }
    
    public function addRole(Role $role)
    {
        if (! $this->hasRole($role->getName()))
        {
            $this->roles->add($role);
        }
    }

    public function hasRole($roleName)
    {
        foreach ($this->roles as $role)
        {
            if ($role->getName() == $roleName)
            {
                return true;
            }
        }

        return false;
    }

    public function getWorkspaces()
    {
        return $this->workspaces->toArray();
    }
    
    public function getWorkspaceCollection()
    {
        return $this->workspaces;
    }
    
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof User) 
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