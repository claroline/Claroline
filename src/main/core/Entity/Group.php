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

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\User\GroupRepository")
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
    use OrganizationsTrait;
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     *
     * @var string
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

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="groups"
     * )
     */
    protected $organizations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     inversedBy="groups"
     * )
     */
    protected $locations;

    /**
     * @ORM\Column(name="is_read_only", type="boolean")
     *
     * @var bool
     */
    protected $isReadOnly = false;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();

        $this->users = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->roles = new ArrayCollection();
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
        if (!$user->getGroups()->contains($this)) {
            $user->getGroups()->add($this);
        }
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

    public function getPlatformRoles()
    {
        $roles = $this->getEntityRoles();
        $return = [];

        foreach ($roles as $role) {
            if (Role::WS_ROLE !== $role->getType()) {
                $return[] = $role;
            }
        }

        return $return;
    }

    public function getOrganizations()
    {
        return $this->organizations;
    }

    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * @return ArrayCollection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get the isReadOnly property.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Get the isReadOnly property.
     *
     * @return bool
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly();
    }

    public function setReadOnly($value)
    {
        $this->isReadOnly = $value;
    }
}
