<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Workspace
{
    const DEFAULT_MAX_STORAGE_SIZE = "1 TB";
    const DEFAULT_MAX_FILE_COUNT = 10000;
    const DEFAULT_MAX_USERS = 10000;

    protected static $visitorPrefix = 'ROLE_WS_VISITOR';
    protected static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    protected static $managerPrefix = 'ROLE_WS_MANAGER';
    protected static $customPrefix = 'ROLE_WS_CUSTOM';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api"})
     * @SerializedName("description")
     */
    protected $description;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Groups({"api"})
     * @SerializedName("code")
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"api"})
     * @SerializedName("maxStorageSize")
     */
    protected $maxStorageSize = "1 TB";

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"api"})
     * @SerializedName("maxUploadResources")
     */
    protected $maxUploadResources = 10000;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"api"})
     * @SerializedName("maxUsers")
     */
    protected $maxUsers = 10000;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api"})
     * @SerializedName("displayable")
     */
    protected $displayable = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="workspace"
     * )
     */
    protected $resources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Model\WorkspaceModel",
     *     mappedBy="workspace"
     * )
     */
    protected $models;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $orderedTools;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     * @Groups({"api"})
     * @SerializedName("creator")
     */
    protected $creator;

    /**
     * @ORM\Column(unique=true)
     * @Groups({"api"})
     * @SerializedName("guid")
     */
    protected $guid;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     * @Groups({"api"})
     * @SerializedName("selfRegistration")
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     * @Groups({"api"})
     * @SerializedName("registrationValidation")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     * @Groups({"api"})
     * @SerializedName("selfUnregistration")
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     * @Groups({"api"})
     * @SerializedName("creationDate")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     * @Groups({"api"})
     * @SerializedName("isPersonal")
     */
    protected $isPersonal = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     * @Groups({"api"})
     * @SerializedName("startDate")
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     * @Groups({"api"})
     * @SerializedName("endDate")
     */
    protected $endDate;

    /**
     * @ORM\Column(name="is_access_date", type="boolean")
     * @Groups({"api"})
     * @SerializedName("isAccessDate")
     */
    protected $isAccessDate = false;

    /**
     * @ORM\Column(name="workspace_type", type="integer", nullable=true)
     * @Groups({"api"})
     * @SerializedName("workspaceType")
     */
    protected $workspaceType;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions",
     *     inversedBy="workspace"
     * )
     * @ORM\JoinColumn(name="options_id", onDelete="SET NULL", nullable=true)
     */
    protected $options;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->orderedTools = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
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

    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate()
    {
        if (is_null($this->creationDate)) {
            return $this->creationDate;
        }

        $date = date('d-m-Y H:i', $this->creationDate);

        return new \Datetime($date);
    }

    /**
     * Sets how many MB can be stored in the workspace
     *
     * @param $maxSize
     */
    public function setMaxStorageSize($maxSize)
    {
        $this->maxStorageSize = $maxSize;
    }

    /**
     * Returns how many MB can be stored in the workspace.
     *
     * @return integer
     */
    public function getMaxStorageSize()
    {
        return $this->maxStorageSize;
    }

    public function setMaxUploadResources($maxSize)
    {
        $this->maxUploadResources = $maxSize;
    }

    public function getMaxUploadResources()
    {
        return $this->maxUploadResources;
    }

    public function setIsPersonal($isPersonal)
    {
        $this->isPersonal = $isPersonal;
    }

    public function isPersonal()
    {
        return $this->isPersonal;
    }

    public function serializeForWidgetPicker()
    {
        $return = array(
            'id' => $this->id,
            'name' => $this->name
        );

        return $return;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function getIsAccessDate()
    {
        return $this->isAccessDate;
    }

    public function setIsAccessDate($isAccessDate)
    {
        $this->isAccessDate = $isAccessDate;
    }

    public function getWorkspaceType()
    {
        return $this->workspaceType;
    }

    public function setWorkspaceType($workspaceType)
    {
        $this->workspaceType = $workspaceType;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function getNameAndCode()
    {
        return $this->name . ' [' . $this->code . ']';
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(WorkspaceOptions $options = null)
    {
        $this->options = $options;
    }

    /**
     * @return null|string
     */
    public function getBackgroundColor()
    {
        $backgroundColor = null;
        $workspaceOptions = $this->getOptions();

        if (null !== $workspaceOptions) {
            $workspaceOptionsDetails = $workspaceOptions->getDetails();

            if (isset($workspaceOptionsDetails['background_color'])) {
                $backgroundColor = $workspaceOptionsDetails['background_color'];
            }
        }

        return $backgroundColor;
    }

    public function __toString()
    {
        return $this->name . ' [' . $this->code . ']';
    }
}
