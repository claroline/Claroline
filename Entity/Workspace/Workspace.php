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
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $maxStorageSize = "1 TB";

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $maxUploadResources = 10000;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $maxUsers = 10000;

    /**
     * @ORM\Column(type="boolean")
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
     */
    protected $creator;

    /**
     * @ORM\Column(unique=true)
     */
    protected $guid;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     */
    protected $isPersonal = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(name="is_access_date", type="boolean")
     */
    protected $isAccessDate = false;

    /**
     * @ORM\Column(name="workspace_type", type="integer", nullable=true)
     */
    protected $workspaceType;

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

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
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
}
