<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;

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

    protected $childType;
    protected $childAction;

    /**
     * Constructor.
     */
    public function __construct($action,
        $details = null,
        $receiver = null,
        $receiverGroup = null,
        $resource = null,
        $role = null,
        $workspace = null,
        $owner = null,
        $toolName = null,
        $childType = null,
        $childAction = null
    )
    {
        $this->action = $action;
        $this->details = $details;
        $this->receiver = $receiver;
        $this->receiverGroup = $receiverGroup;
        $this->resource = $resource;
        $this->role = $role;
        $this->workspace = $workspace;
        $this->owner = $owner;
        $this->toolName = $toolName;
        $this->childType = $childType;
        $this->childAction = $childAction;
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
     * Returns the sub entity's type in case of indirect resource update 
     * (creation/delete/update of plugin's sub entities; e.g. In a forum we have threads and posts as sub entities)
     */
    public function getChildType()
    {
        return $this->childType;
    }

    /**
     * Returns the sub entity's action name in case of indirect resource update 
     * (creation/delete/update of plugin's sub entities; e.g. 
     * In a forum we can have thread creation, post publication etc.)
     */
    public function getChildAction()
    {
        return $this->childAction;
    }
}