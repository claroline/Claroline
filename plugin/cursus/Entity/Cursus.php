<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CursusRepository")
 * @ORM\Table(name="claro_cursusbundle_cursus")
 * @Gedmo\Tree(type="nested")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Cursus
{
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     */
    protected $id;

    /**
     * @ORM\Column(unique=true, nullable=true)
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("code")
     */
    protected $code;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("title")
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("description")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"api_cursus", "api_workspace_min"})
     */
    protected $course;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("blocking")
     */
    protected $blocking = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     */
    protected $details;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Cursus",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_workspace_min"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\Cursus",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"cursusOrder" = "ASC"})
     * @Groups({"api_cursus", "api_workspace_min"})
     */
    protected $children;

    /**
     * @ORM\Column(name="cursus_order", type="integer")
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("cursusOrder")
     */
    protected $cursusOrder;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CursusUser",
     *     mappedBy="cursus"
     * )
     */
    protected $cursusUsers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CursusGroup",
     *     mappedBy="cursus"
     * )
     */
    protected $cursusGroups;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     */
    protected $icon;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_workspace_min"})
     */
    protected $workspace;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("root")
     */
    private $root;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @Groups({"api_cursus", "api_user_min", "api_workspace_min"})
     * @SerializedName("lvl")
     */
    private $lvl;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("lft")
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("rgt")
     */
    private $rgt;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_cursus_organizations")
     * @Groups({"api_cursus", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("organizations")
     */
    protected $organizations;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->cursusUsers = new ArrayCollection();
        $this->cursusGroups = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course = null)
    {
        $this->course = $course;
    }

    public function isBlocking()
    {
        return $this->blocking;
    }

    public function setBlocking($blocking)
    {
        $this->blocking = $blocking;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Cursus $parent = null)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children->toArray();
    }

    public function addChild(Cursus $cursus)
    {
        if (!$this->children->contains($cursus)) {
            $this->children->add($cursus);
        }
    }

    public function getCursusOrder()
    {
        return $this->cursusOrder;
    }

    public function setCursusOrder($cursusOrder)
    {
        $this->cursusOrder = $cursusOrder;
    }

    public function getCursusUsers()
    {
        return $this->cursusUsers->toArray();
    }

    public function getCursusGroups()
    {
        return $this->cursusGroups->toArray();
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    public function getOrganizations()
    {
        return $this->organizations->toArray();
    }

    public function addOrganization(Organization $organization)
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization)
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    public function emptyOrganizations()
    {
        $this->organizations->clear();
    }

    public function getTitleAndCode()
    {
        $result = $this->title;

        if (!is_null($this->code)) {
            $result .= ' ['.$this->code.']';
        }

        return $result;
    }

    /**
     * @Groups({"api", "api_cursus", "api_workspace_min"})
     * @VirtualProperty
     * @SerializedName("parentId")
     */
    public function getParentId()
    {
        return is_null($this->parent) ? null : $this->parent->getId();
    }

    public function __toString()
    {
        return $this->getTitleAndCode();
    }
}
