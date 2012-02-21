<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;

// TODO: Implements AdvancedUserInterface

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\Table(name="claro_user")
 * @DoctrineAssert\UniqueEntity("username")
 */
class User extends AbstractRoleSubject implements UserInterface
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
     *      targetEntity="Claroline\CoreBundle\Entity\Group", 
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

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
        parent::__construct();
        $this->groups = new ArrayCollection();
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
        $this->password = null;
    }
    
    public function getGroups()
    {
        return $this->groups;
    }
    
    /**
     * Returns the user's roles (including role's ancestors) as an array 
     * of string values (needed for Symfony security checks). The roles
     * owned by groups which the user belong to are also included.
     * 
     * @return array[string]
     */
    public function getRoles()
    {
        $roleNames = array();
        
        foreach ($this->getOwnedRoles(true) as $role)
        {
            $roleNames[] = $role->getName();
        }
        
        foreach ($this->getGroups() as $group)
        {
            foreach ($group->getOwnedRoles(true) as $role)
            {
                $roleNames[] = $role->getName();
            }
        }
        
        return array_unique($roleNames);
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
        if (in_array($roleName, $this->getRoles()))
        {
            return true;
        }
        
        return false;
    }

    public function getWorkspaceRoleCollection()
    {
        return $this->workspaceRoles;
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