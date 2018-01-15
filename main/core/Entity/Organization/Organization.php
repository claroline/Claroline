<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Organization;

use Claroline\CoreBundle\Entity\Calendar\TimeSlot;
use Claroline\CoreBundle\Entity\Calendar\Year;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Model\CodeTrait;
use Claroline\CoreBundle\Entity\Model\GroupsTrait;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Organization\OrganizationRepository")
 * @ORM\Table(name="claro__organization")
 * @DoctrineAssert\UniqueEntity("name")
 * @Gedmo\Tree(type="nested")
 */
class Organization
{
    use UuidTrait;
    use CodeTrait;
    use GroupsTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"api_user", "api_user_min", "api_workspace_min", "api_group_min", "api_organization_tree", "api_organization_list"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Groups({"api_organization_tree", "api_organization_list"})
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Serializer\Groups({"api_user", "api_user_min", "api_workspace_min", "api_group_min", "api_organization_tree", "api_organization_list"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true, type="string")
     * @Assert\Email()
     * @Serializer\Groups({"api_organization_tree", "api_organization_list"})
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Location",
     *     cascade={"persist"},
     *     inversedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro__location_organization")
     * @Serializer\Groups({"api_organization_tree", "api_organization_list"})
     *
     * @var ArrayCollection
     */
    protected $locations;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     *
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     *
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     *
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     *
     * @var int
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"api_organization_tree"})
     *
     * @var Organization
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @Serializer\Groups({"api_organization_tree"})
     *
     * @var Organization[]|ArrayCollection
     */
    private $children;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_user_organization")
     *
     * @var ArrayCollection
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_user_workspace")
     *
     * @var Workspace[]|ArrayCollection
     */
    protected $workspaces;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_group_organization")
     *
     * @var ArrayCollection
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User", mappedBy="administratedOrganizations")
     * @Serializer\Groups({"api_organization_tree", "api_organization_list"})
     *
     * @var User[]|ArrayCollection
     */
    protected $administrators;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\TimeSlot",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     *
     * @var TimeSlot[]|ArrayCollection
     */
    protected $timeSlots;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\Year",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     *
     * @var Year[]|ArrayCollection
     */
    protected $years;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     * @Serializer\Groups({"api_user", "api_user_min", "api_workspace_min", "api_group_min", "api_organization_tree", "api_organization_list"})
     */
    protected $default = false;

    public function __construct()
    {
        $this->refreshUuid();
        $this->refreshCode();

        $this->locations = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->timeSlots = new ArrayCollection();
        $this->years = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get parent.
     *
     * @return Organization
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param Organization $parent
     */
    public function setParent(Organization $parent = null)
    {
        $this->parent = $parent;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setLocations($locations)
    {
        $this->locations = $locations;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function getAdministrators()
    {
        return $this->administrators;
    }

    public function addAdministrator(User $user)
    {
        if (!$this->administrators->contains($user)) {
            $this->administrators->add($user);
            $user->addAdministratedOrganization($this);
        }
    }

    public function removeAdministrator(User $user)
    {
        if ($this->administrators->contains($user)) {
            $this->administrators->removeElement($user);
            $user->removeAdministratedOrganization($this);
        }
    }

    public function setAdministrators(ArrayCollection $users)
    {
        $this->administrators = $users;
    }

    public function getTimeSlots()
    {
        return $this->timeSlots;
    }

    public function addTimeSlot(TimeSlot $timeSlot)
    {
        if (!$this->timeSlots->contains($timeSlot)) {
            $this->timeSlots->add($timeSlot);
        }
    }

    public function removeTimeSlot(TimeSlot $timeSlot)
    {
        if ($this->timeSlots->contains($timeSlot)) {
            $this->timeSlots->removeElement($timeSlot);
        }
    }

    public function setTimeSlots(ArrayCollection $timeSlots)
    {
        $this->timeSlots = $timeSlots;
    }

    public function getYears()
    {
        return $this->years;
    }

    public function addYear(Year $year)
    {
        if (!$this->years->contains($year)) {
            $this->years->add($year);
        }
    }

    public function removeYear(Year $year)
    {
        if ($this->years->contains($year)) {
            $this->years->removeElement($year);
        }
    }

    public function setYears(ArrayCollection $years)
    {
        $this->years = $years;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function isDefault()
    {
        return $this->default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users->add($user);
        $user->getUserOrganizations()->add($this);
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->remove($user);
        $user->getUserOrganizations()->removeElement($this);
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);
        $group->getOrganizations()->add($this);
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->remove($group);
        $group->getOrganizations()->removeElement($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getWorkspaces()
    {
        return $this->workspaces;
    }
}
