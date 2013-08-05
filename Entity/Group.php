<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\GroupRepository")
 * @ORM\Table(
 *      name="claro_group",
*       uniqueConstraints={
 *          @ORM\UniqueConstraint(name="group_unique_name", columns={"name"})
 *      }
 *  )
 * @DoctrineAssert\UniqueEntity("name")
 */
class Group extends AbstractRoleSubject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     mappedBy="groups"
     * )
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"},
     *     inversedBy="groups"
     * )
     * @ORM\JoinTable(name="claro_group_role")
     */
    protected $roles;

    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addUser(User $user)
    {
        $user->getGroups()->add($this);
    }

    public function removeUser(User $user)
    {
        $user->getGroups()->removeElement($this);
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                return $role;
            }
        }
    }

    public function setPlatformRole($platformRole)
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                $removedRole = $role;
            }
        }

        if (isset($removedRole)) {
            $this->roles->removeElement($removedRole);
        }

        $this->roles->add($platformRole);
    }

    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }
}
