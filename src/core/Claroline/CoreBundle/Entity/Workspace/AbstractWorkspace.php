<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SerializerBundle\Annotation\Type;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
 *     "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 * })
 * @DoctrineAssert\UniqueEntity("code")
 */
abstract class AbstractWorkspace
{
    protected static $visitorPrefix = 'ROLE_WS_VISITOR';
    protected static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    protected static $managerPrefix = 'ROLE_WS_MANAGER';
    protected static $customPrefix = 'ROLE_WS_CUSTOM';

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
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(name="is_public", type="boolean", nullable=true)
     */
    protected $isPublic = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $displayable;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     mappedBy="workspace"
     * )
     */
    protected $resources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Event",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $events;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $orderedTools;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $roles;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="personalWorkspace"
     * )
     */
    protected $personalUser;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     */
    protected $creator;

    /**
     * @ORM\Column(unique=true)
     */
    protected $guid;

    /**
     * @ORM\Column(name="self_registration", type="boolean", nullable=true)
     */
    protected $selfRegistration;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean", nullable=true)
     */
    protected $selfUnregistration;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->orderedTools = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = 0;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    abstract public function setPublic($isPublic);

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    public function addOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->add($tool);
    }

    public function removeOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->removeElement($tool);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    public function isDisplayable()
    {
        return $this->displayable;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }
}
