<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Log\LogRepository")
 * @ORM\Table(name="claro_log", indexes={
 *     @Index(name="action_idx", columns={"action"}),
 *     @Index(name="tool_idx", columns={"tool_name"}),
 *     @Index(name="doer_type_idx", columns={"doer_type"})
 * })
 */
class Log
{
    const doerTypeAnonymous = 'anonymous';
    const doerTypeUser = 'user';
    const doerTypePlatform = 'platform';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     */
    protected $action;

    /**
     * @ORM\Column(name="date_log", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $dateLog;

    /**
     * @ORM\Column(name="short_date_log", type="date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $shortDateLog;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="doer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $doer;

    /**
     * @ORM\Column(name="doer_type")
     */
    protected $doerType;

    /**
     * @ORM\Column(name="doer_ip", nullable=true)
     */
    protected $doerIp;

    /**
     * @ORM\Column(name="doer_session_id", nullable=true)
     */
    protected $doerSessionId;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_log_doer_platform_roles")
     */
    protected $doerPlatformRoles;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_log_doer_workspace_roles")
     */
    protected $doerWorkspaceRoles;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="receiver_group_id", onDelete="SET NULL")
     */
    protected $receiverGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="tool_name", nullable=true)
     */
    protected $toolName;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode", inversedBy="logs")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="SET NULL")
     */
    protected $resourceType;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $role;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_displayed_in_admin", type="boolean")
     */
    protected $isDisplayedInAdmin = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_displayed_in_workspace", type="boolean")
     */
    protected $isDisplayedInWorkspace = false;

    /**
     * @ORM\Column(name="other_element_id", type="integer", nullable=true)
     */
    protected $otherElementId;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->doerPlatformRoles = new ArrayCollection();
        $this->doerWorkspaceRoles = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set action.
     *
     * @param string $action
     *
     * @return Log
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets the log creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setDateLog(\DateTime $date)
    {
        $this->dateLog = $date;
        $this->shortDateLog = $date;
    }

    /**
     * Get dateLog.
     *
     * @return \DateTime
     */
    public function getDateLog()
    {
        return $this->dateLog;
    }

    /**
     * Set details.
     *
     * @param array $details
     *
     * @return Log
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set doerType.
     *
     * @param string $doerType
     *
     * @return Log
     */
    public function setDoerType($doerType)
    {
        $this->doerType = $doerType;

        return $this;
    }

    /**
     * Get doerType.
     *
     * @return string
     */
    public function getDoerType()
    {
        return $this->doerType;
    }

    /**
     * Set doerIp.
     *
     * @param string $doerIp
     *
     * @return Log
     */
    public function setDoerIp($doerIp)
    {
        $this->doerIp = $doerIp;

        return $this;
    }

    /**
     * Get doerIp.
     *
     * @return string
     */
    public function getDoerIp()
    {
        return $this->doerIp;
    }

    /**
     * Set doerSessionId.
     *
     * @param string $doerSessionId
     *
     * @return Log
     */
    public function setDoerSessionId($doerSessionId)
    {
        $this->doerSessionId = $doerSessionId;

        return $this;
    }

    /**
     * Get doerSessionId.
     *
     * @return string
     */
    public function getDoerSessionId()
    {
        return $this->doerSessionId;
    }

    /**
     * Set doer.
     *
     * @param User $doer
     *
     * @return Log
     */
    public function setDoer(User $doer = null)
    {
        $this->doer = $doer;

        return $this;
    }

    /**
     * Get doer.
     *
     * @return User
     */
    public function getDoer()
    {
        return $this->doer;
    }

    /**
     * Add doerPlatformRoles.
     *
     * @param Role $doerPlatformRoles
     *
     * @return Log
     */
    public function addDoerPlatformRole(Role $doerPlatformRoles)
    {
        $this->doerPlatformRoles[] = $doerPlatformRoles;

        return $this;
    }

    /**
     * Remove doerPlatformRoles.
     *
     * @param Role $doerPlatformRoles
     */
    public function removeDoerPlatformRole(Role $doerPlatformRoles)
    {
        $this->doerPlatformRoles->removeElement($doerPlatformRoles);
    }

    /**
     * Get doerPlatformRoles.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDoerPlatformRoles()
    {
        return $this->doerPlatformRoles;
    }

    /**
     * Add doerWorkspaceRoles.
     *
     * @param Role $doerWorkspaceRoles
     *
     * @return Log
     */
    public function addDoerWorkspaceRole(Role $doerWorkspaceRoles)
    {
        $this->doerWorkspaceRoles[] = $doerWorkspaceRoles;

        return $this;
    }

    /**
     * Remove doerWorkspaceRoles.
     *
     * @param Role $doerWorkspaceRoles
     */
    public function removeDoerWorkspaceRole(Role $doerWorkspaceRoles)
    {
        $this->doerWorkspaceRoles->removeElement($doerWorkspaceRoles);
    }

    /**
     * Get doerWorkspaceRoles.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDoerWorkspaceRoles()
    {
        return $this->doerWorkspaceRoles;
    }

    /**
     * Set receiver.
     *
     * @param User $receiver
     *
     * @return Log
     */
    public function setReceiver(User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver.
     *
     * @return User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set receiverGroup.
     *
     * @param Group $receiverGroup
     *
     * @return Log
     */
    public function setReceiverGroup(Group $receiverGroup = null)
    {
        $this->receiverGroup = $receiverGroup;

        return $this;
    }

    /**
     * Get receiverGroup.
     *
     * @return Group
     */
    public function getReceiverGroup()
    {
        return $this->receiverGroup;
    }

    /**
     * Set owner.
     *
     * @param User $owner
     *
     * @return Log
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set workspace.
     *
     * @param Workspace $workspace
     *
     * @return Log
     */
    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set resource.
     *
     * @param ResourceNode $resourceNode
     *
     * @return Log
     */
    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resource.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Set resourceType.
     *
     * @param ResourceType $resourceType
     *
     * @return Log
     */
    public function setResourceType(ResourceType $resourceType = null)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * Get resourceType.
     *
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Set role.
     *
     * @param Role $role
     *
     * @return Log
     */
    public function setRole(Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set toolName.
     *
     * @param string $toolName
     *
     * @return Log
     */
    public function setToolName($toolName)
    {
        $this->toolName = $toolName;

        return $this;
    }

    /**
     * Get toolName.
     *
     * @return string
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * @param mixed $isDisplayedInAdmin
     *
     * @return Log
     */
    public function setIsDisplayedInAdmin($isDisplayedInAdmin)
    {
        $this->isDisplayedInAdmin = $isDisplayedInAdmin;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayedInAdmin()
    {
        return $this->isDisplayedInAdmin;
    }

    /**
     * @param bool $isDisplayedInWorkspace
     *
     * @return Log
     */
    public function setIsDisplayedInWorkspace($isDisplayedInWorkspace)
    {
        $this->isDisplayedInWorkspace = $isDisplayedInWorkspace;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayedInWorkspace()
    {
        return $this->isDisplayedInWorkspace;
    }

    /**
     * @return int
     */
    public function getOtherElementId()
    {
        return $this->otherElementId;
    }

    /**
     * @param int $otherElementId
     *
     * @return Log
     */
    public function setOtherElementId($otherElementId)
    {
        $this->otherElementId = $otherElementId;

        return $this;
    }
}
