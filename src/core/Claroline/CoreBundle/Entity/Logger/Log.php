<?php

namespace Claroline\CoreBundle\Entity\Logger;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\LogRepository")
 * @ORM\Table(name="claro_log")
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="tool_name", nullable=true)
     */
    protected $toolName;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
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
     * @ORM\Column(name="child_type", nullable=true)
     */
    protected $childType;

    /**
     * @ORM\Column(name="child_action", nullable=true)
     */
    protected $childAction;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $role;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->doerPlatformRoles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->doerWorkspaceRoles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set action
     *
     * @param  string $action
     * @return Log
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
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
     * Get dateLog
     *
     * @return \DateTime
     */
    public function getDateLog()
    {
        return $this->dateLog;
    }

    /**
     * Set details
     *
     * @param  array $details
     * @return Log
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set doerType
     *
     * @param  string $doerType
     * @return Log
     */
    public function setDoerType($doerType)
    {
        $this->doerType = $doerType;

        return $this;
    }

    /**
     * Get doerType
     *
     * @return string
     */
    public function getDoerType()
    {
        return $this->doerType;
    }

    /**
     * Set doerIp
     *
     * @param  string $doerIp
     * @return Log
     */
    public function setDoerIp($doerIp)
    {
        $this->doerIp = $doerIp;

        return $this;
    }

    /**
     * Get doerIp
     *
     * @return string
     */
    public function getDoerIp()
    {
        return $this->doerIp;
    }

    /**
     * Set childType
     *
     * @param  string $childType
     * @return Log
     */
    public function setChildType($childType)
    {
        $this->childType = $childType;

        return $this;
    }

    /**
     * Get childType
     *
     * @return string
     */
    public function getChildType()
    {
        return $this->childType;
    }

    /**
     * Set childAction
     *
     * @param  string $childAction
     * @return Log
     */
    public function setChildAction($childAction)
    {
        $this->childAction = $childAction;

        return $this;
    }

    /**
     * Get childAction
     *
     * @return string
     */
    public function getChildAction()
    {
        return $this->childAction;
    }

    /**
     * Set doer
     *
     * @param  \Claroline\CoreBundle\Entity\User $doer
     * @return Log
     */
    public function setDoer(\Claroline\CoreBundle\Entity\User $doer = null)
    {
        $this->doer = $doer;

        return $this;
    }

    /**
     * Get doer
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getDoer()
    {
        return $this->doer;
    }

    /**
     * Add doerPlatformRoles
     *
     * @param  \Claroline\CoreBundle\Entity\Role $doerPlatformRoles
     * @return Log
     */
    public function addDoerPlatformRole(\Claroline\CoreBundle\Entity\Role $doerPlatformRoles)
    {
        $this->doerPlatformRoles[] = $doerPlatformRoles;

        return $this;
    }

    /**
     * Remove doerPlatformRoles
     *
     * @param \Claroline\CoreBundle\Entity\Role $doerPlatformRoles
     */
    public function removeDoerPlatformRole(\Claroline\CoreBundle\Entity\Role $doerPlatformRoles)
    {
        $this->doerPlatformRoles->removeElement($doerPlatformRoles);
    }

    /**
     * Get doerPlatformRoles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDoerPlatformRoles()
    {
        return $this->doerPlatformRoles;
    }

    /**
     * Add doerWorkspaceRoles
     *
     * @param  \Claroline\CoreBundle\Entity\Role $doerWorkspaceRoles
     * @return Log
     */
    public function addDoerWorkspaceRole(\Claroline\CoreBundle\Entity\Role $doerWorkspaceRoles)
    {
        $this->doerWorkspaceRoles[] = $doerWorkspaceRoles;

        return $this;
    }

    /**
     * Remove doerWorkspaceRoles
     *
     * @param \Claroline\CoreBundle\Entity\Role $doerWorkspaceRoles
     */
    public function removeDoerWorkspaceRole(\Claroline\CoreBundle\Entity\Role $doerWorkspaceRoles)
    {
        $this->doerWorkspaceRoles->removeElement($doerWorkspaceRoles);
    }

    /**
     * Get doerWorkspaceRoles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDoerWorkspaceRoles()
    {
        return $this->doerWorkspaceRoles;
    }

    /**
     * Set receiver
     *
     * @param  \Claroline\CoreBundle\Entity\User $receiver
     * @return Log
     */
    public function setReceiver(\Claroline\CoreBundle\Entity\User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set receiverGroup
     *
     * @param  \Claroline\CoreBundle\Entity\Group $receiverGroup
     * @return Log
     */
    public function setReceiverGroup(\Claroline\CoreBundle\Entity\Group $receiverGroup = null)
    {
        $this->receiverGroup = $receiverGroup;

        return $this;
    }

    /**
     * Get receiverGroup
     *
     * @return \Claroline\CoreBundle\Entity\Group
     */
    public function getReceiverGroup()
    {
        return $this->receiverGroup;
    }

    /**
     * Set owner
     *
     * @param  \Claroline\CoreBundle\Entity\User $owner
     * @return Log
     */
    public function setOwner(\Claroline\CoreBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set workspace
     *
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return Log
     */
    public function setWorkspace(\Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set resource
     *
     * @param  \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @return Log
     */
    public function setResourceNode(\Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Set resourceType
     *
     * @param  \Claroline\CoreBundle\Entity\Resource\ResourceType $resourceType
     * @return Log
     */
    public function setResourceType(\Claroline\CoreBundle\Entity\Resource\ResourceType $resourceType = null)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * Get resourceType
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Set role
     *
     * @param  \Claroline\CoreBundle\Entity\Role $role
     * @return Log
     */
    public function setRole(\Claroline\CoreBundle\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set toolName
     *
     * @param  string $toolName
     * @return Log
     */
    public function setToolName($toolName)
    {
        $this->toolName = $toolName;

        return $this;
    }

    /**
     * Get toolName
     *
     * @return string
     */
    public function getToolName()
    {
        return $this->toolName;
    }
}
