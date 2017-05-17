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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

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
class Group extends AbstractRoleSubject implements OrderableInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_group", "api_group_min"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_group", "api_group_min"})
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
     * @ORM\Column()
     * @Groups({"api_group", "api_group_min"})
     */
    protected $guid;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"},
     *     inversedBy="groups"
     * )
     * @ORM\JoinTable(name="claro_group_role")
     * @Groups({"api_group"})
     */
    protected $roles;

    /**
     * @var Organization[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     */
    protected $organizations;

    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
        $this->organizations = new ArrayCollection();
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

    public function getUserIds()
    {
        $users = $this->getUsers();
        $userIds = [];
        foreach ($users as $user) {
            array_push($userIds, $user->getId());
        }

        return $userIds;
    }

    /**
     * alias for getPlateformeRole.
     */
    public function getPlatformRoles()
    {
        return $this->getPlatformRole();
    }

    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();
        $return = [];

        foreach ($roles as $role) {
            if ($role->getType() !== Role::WS_ROLE) {
                $return[] = $role;
            }
        }

        return $return;
    }

    public function setPlatformRole($platformRole)
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() !== Role::WS_ROLE) {
                $removedRole = $role;
            }
        }

        if (isset($removedRole)) {
            $this->roles->removeElement($removedRole);
        }

        $this->roles->add($platformRole);
    }

    /**
     * Replace the old platform roles of a user by a new array.
     *
     * @param $platformRoles
     */
    public function setPlatformRoles($platformRoles)
    {
        $roles = $this->getEntityRoles();
        $removedRoles = [];

        foreach ($roles as $role) {
            if ($role->getType() !== Role::WS_ROLE) {
                $removedRoles[] = $role;
            }
        }

        foreach ($removedRoles as $removedRole) {
            $this->roles->removeElement($removedRole);
        }

        foreach ($platformRoles as $platformRole) {
            $this->roles->add($platformRole);
        }
    }

    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function getOrderableFields()
    {
        return ['name', 'id'];
    }

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public static function getSearchableFields()
    {
        return ['name'];
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getOrganizations()
    {
        return $this->organizations;
    }

    public function setOrganizations(ArrayCollection $organizations)
    {
        $this->organizations = $organizations;
    }

    public function addOrganization(Organization $organization)
    {
        $this->organizations->add($organization);
    }
}
