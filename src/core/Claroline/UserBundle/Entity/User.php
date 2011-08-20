<?php

namespace Claroline\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Claroline\SecurityBundle\Entity\Role;

// TODO: Implements AdvancedUserInterface

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_user")
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
     * @Assert\NotBlank(message = "FIRST NAME NOT BLANK TEST")
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @ORM\Column(name="username", type="string", length="255")
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

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $userRole = new Role();
        $userRole->setName('ROLE_USER');
        $this->roles = array($userRole);
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

    public function getRoles()
    {
        return $this->roles->toArray();
    }
    
    public function addRole(Role $role)
    {
        if (! in_array($role, $this->getRoles()))
        {
            $this->roles->add($role);
        }
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->firstName !== $user->getFirstName()) {
            return false;
        }

        if ($this->lastName !== $user->getLastName()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }
    }

    // TODO: use a listener to check unique constraints
    /**
     * @Assert\True(message = "USERNAME ALREADY EXISTS TEST")
     */
    public function isUsernameUnique()
    {
        // search for current username property in db
        return true;
    }
}