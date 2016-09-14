<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

abstract class LogGenericEvent extends Event implements RestrictionnableInterface
{
    const DISPLAYED_ADMIN = 'admin';
    const DISPLAYED_WORKSPACE = 'workspace';

    const PLATFORM_EVENT_TYPE = 'platform';

    protected $action;

    protected $details;
    protected $receiver;
    protected $receiverGroup;
    protected $resource;
    protected $role;
    protected $workspace;
    protected $owner;
    protected $toolName;

    protected $doer;

    /** @var bool */
    protected $isDisplayedInAdmin = false;

    /** @var bool */
    protected $isDisplayedInWorkspace = false;

    /** @var bool */
    protected $isWorkspaceEnterEvent = false;

    /**
     * @var int
     */
    protected $otherElementId = null;

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
        Workspace $workspace = null,
        User $owner = null,
        $toolName = null,
        $isWorkspaceEnterEvent = false,
        $elementId = null
    ) {
        $this->action = $action;
        $this->details = $details;
        $this->receiver = $receiver;
        $this->receiverGroup = $receiverGroup;
        $this->resource = $resource;
        $this->role = $role;
        $this->workspace = $workspace;
        $this->owner = $owner;
        $this->toolName = $toolName;
        $this->isWorkspaceEnterEvent = $isWorkspaceEnterEvent;
        $this->otherElementId = $elementId;

        $this->setVisibilityFromRestriction();
    }

    /**
     * Returns the action's name.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns details (array) containing the particular info of the action.
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Returns the action's target user.
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Sets receiver's data.
     *
     * @param User $receiver
     *
     * @return $this
     */
    public function setReceiver(User $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Returns the action's target group.
     */
    public function getReceiverGroup()
    {
        return $this->receiverGroup;
    }

    /**
     * Returns the action's target resource.
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the action's target role.
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the action's target workspace.
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Returns the action's target owner (from resource).
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Returns the action's target tool's name.
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * @return LogGenericEvent
     */
    public function setVisibilityFromRestriction()
    {
        $this->isDisplayedInAdmin = true;
        $this->isDisplayedInWorkspace = true;

        // only take admin restriction into account
        // TODO: refactor the log system to reflect that change (i.e. events are
        // displayable everywhere, unless they're marked as "admin" events)
        if (($restrictions = $this->getRestriction())
            && count($restrictions) === 1
            && $restrictions[0] === self::DISPLAYED_ADMIN) {
            $this->isDisplayedInWorkspace = false;
        }

        return $this;
    }

    /**
     * Return the visibility in admin for the associated log.
     *
     * @return bool
     */
    public function getIsDisplayedInAdmin()
    {
        return $this->isDisplayedInAdmin;
    }

    /**
     * Return the visibility in workspace for the associated log.
     *
     * @return bool
     */
    public function getIsDisplayedInWorkspace()
    {
        return $this->isDisplayedInWorkspace;
    }

    /**
     * Return the visibility in workspace for the associated log.
     *
     * @return bool
     */
    public function getIsWorkspaceEnterEvent()
    {
        return $this->isWorkspaceEnterEvent;
    }

    /**
     * @param bool $isDisplayedInAdmin
     *
     * @return LogGenericEvent
     */
    public function setIsDisplayedInAdmin($isDisplayedInAdmin)
    {
        $this->isDisplayedInAdmin = $isDisplayedInAdmin;

        return $this;
    }

    /**
     * @param bool $isDisplayedInWorkspace
     *
     * @return LogGenericEvent
     */
    public function setIsDisplayedInWorkspace($isDisplayedInWorkspace)
    {
        $this->isDisplayedInWorkspace = $isDisplayedInWorkspace;

        return $this;
    }

    /**
     * Used when the doer isn't the logged user.
     *
     * @param User $doer
     */
    public function setDoer(User $doer)
    {
        $this->doer = $doer;
    }

    /**
     * @return User
     */
    public function getDoer()
    {
        return $this->doer;
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
     * @return LogGenericEvent
     */
    public function setOtherElementId($otherElementId)
    {
        $this->otherElementId = $otherElementId;

        return $this;
    }
}
