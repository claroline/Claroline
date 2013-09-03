<?php

namespace Claroline\CoreBundle\Event\Log;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;

class LogGenericEvent extends Event
{
    protected $action;

    protected $details;
    protected $receiver;
    protected $receiverGroup;
    protected $resource;
    protected $role;
    protected $workspace;
    protected $owner;
    protected $toolName;

    /** @var bool */
    protected $isDisplayedInAdmin = false;

    /** @var bool */
    protected $isDisplayedInWorkspace = false;

    /**
     * Constructor.
     */
    public function __construct(
        $action,
        $details = null,
        User $receiver = null,
        $receiverGroup = null,
        ResourceNode $resource = null,
        Role $role = null,
        AbstractWorkspace $workspace = null,
        User $owner = null,
        $toolName = null
    )
    {
        $this->action                 = $action;
        $this->details                = $details;
        $this->receiver               = $receiver;
        $this->receiverGroup          = $receiverGroup;
        $this->resource               = $resource;
        $this->role                   = $role;
        $this->workspace              = $workspace;
        $this->owner                  = $owner;
        $this->toolName               = $toolName;
    }

    /**
     * Returns the action's name
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns details (array) containing the particular info of the action
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Returns the action's target user
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Returns the action's target group
     */
    public function getReceiverGroup()
    {
        return $this->receiverGroup;
    }

    /**
     * Returns the action's target resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the action's target role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the action's target workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Returns the action's target owner (from resource)
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Returns the action's target tool's name
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * Return the visibility in admin for the associated log
     *
     * @return bool
     */
    public function getIsDisplayedInAdmin()
    {
        return $this->isDisplayedInAdmin;
    }

    /**
     * Return the visibility in workspace for the associated log
     *
     * @return bool
     */
    public function getIsDisplayedInWorkspace()
    {
        return $this->isDisplayedInWorkspace;
    }

    /**
     * @param boolean $isDisplayedInAdmin
     *
     * @return LogGenericEvent
     */
    public function isDisplayedInAdmin($isDisplayedInAdmin)
    {
        $this->isDisplayedInAdmin = $isDisplayedInAdmin;

        return $this;
    }

    /**
     * @param boolean $isDisplayedInWorkspace
     *
     * @return LogGenericEvent
     */
    public function isDisplayedInWorkspace($isDisplayedInWorkspace)
    {
        $this->isDisplayedInWorkspace = $isDisplayedInWorkspace;

        return $this;
    }


}
