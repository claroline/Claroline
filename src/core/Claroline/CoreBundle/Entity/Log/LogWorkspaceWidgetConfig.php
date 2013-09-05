<?php

namespace Claroline\CoreBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_workspace_widget_config")
 */
class LogWorkspaceWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="integer")
     */
    protected $amount = 5;

    // CREATION
    /**
     * @ORM\Column(name="resource_copy", type="boolean")
     */
    protected $resourceCopy = true;

    /**
     * @ORM\Column(name="resource_create", type="boolean")
     */
    protected $resourceCreate = true;

    /**
     * @ORM\Column(name="resource_shortcut", type="boolean")
     */
    protected $resourceShortcut = true;

    // READ
    /**
     * @ORM\Column(name="resource_read", type="boolean")
     */
    protected $resourceRead = true;

    /**
     * @ORM\Column(name="ws_tool_read", type="boolean")
     */
    protected $wsToolRead = true;

    // DOWNLOAD
    /**
     * @ORM\Column(name="resource_export", type="boolean")
     */
    protected $resourceExport = true;

    // UPDATE
    /**
     * @ORM\Column(name="resource_update", type="boolean")
     */
    protected $resourceUpdate = true;

    /**
     * @ORM\Column(name="resource_update_rename", type="boolean")
     */
    protected $resourceUpdateRename = true;

    // NEW IN THE OBJECT
    /**
     * @ORM\Column(name="resource_child_update", type="boolean")
     */
    protected $resourceChildUpdate = true;

    // DELETE
    /**
     * @ORM\Column(name="resource_delete", type="boolean")
     */
    protected $resourceDelete = true;

    // MOVE
    /**
     * @ORM\Column(name="resource_move", type="boolean")
     */
    protected $resourceMove = true;

    // INSCRIPTION
    /**
     * @ORM\Column(name="ws_role_subscribe_user", type="boolean")
     */
    protected $wsRoleSubscribeUser = true;

    /**
     * @ORM\Column(name="ws_role_subscribe_group", type="boolean")
     */
    protected $wsRoleSubscribeGroup = true;

    /**
     * @ORM\Column(name="ws_role_unsubscribe_user", type="boolean")
     */
    protected $wsRoleUnsubscribeUser = true;

    /**
     * @ORM\Column(name="ws_role_unsubscribe_group", type="boolean")
     */
    protected $wsRoleUnsubscribeGroup = true;

    /**
     * @ORM\Column(name="ws_role_change_right", type="boolean")
     */
    protected $wsRoleChangeRight = true;

    /**
     * @ORM\Column(name="ws_role_create", type="boolean")
     */
    protected $wsRoleCreate = true;

    /**
     * @ORM\Column(name="ws_role_delete", type="boolean")
     */
    protected $wsRoleDelete = true;

    /**
     * @ORM\Column(name="ws_role_update", type="boolean")
     */
    protected $wsRoleUpdate = true;

    // IGNORE
    // group_add_user
    // group_create
    // group_delete
    // group_remove_user
    // group_update

    // user_create
    // user_delete
    // user_login
    // user_update

    // workspace_create
    // workspace_delete
    // workspace_update

    public function getActionRestriction()
    {
        $actionRestriction = array();
        if ($this->getResourceCopy() === true) {
            $actionRestriction[] = 'resource-copy';
        }
        if ($this->getResourceCreate() === true) {
            $actionRestriction[] = 'resource-create';
        }
        if ($this->getResourceShortcut() === true) {
            $actionRestriction[] = 'resource-shortcut';
        }
        if ($this->getResourceRead() === true) {
            $actionRestriction[] = 'resource-read';
        }
        if ($this->getWsToolRead() === true) {
            $actionRestriction[] = 'workspace-tool_read';
        }
        if ($this->getResourceExport() === true) {
            $actionRestriction[] = 'resource-export';
        }
        if ($this->getResourceUpdate() === true) {
            $actionRestriction[] = 'resource-update';
        }
        if ($this->getResourceUpdateRename() === true) {
            $actionRestriction[] = 'resource-update_rename';
        }
        if ($this->getResourceChildUpdate() === true) {
            $actionRestriction[] = 'resource-child_update';
        }
        if ($this->getResourceDelete() === true) {
            $actionRestriction[] = 'resource-delete';
        }
        if ($this->getResourceMove() === true) {
            $actionRestriction[] = 'resource-move';
        }
        if ($this->getWsRoleSubscribeUser() === true) {
            $actionRestriction[] = 'workspace-role-subscribe_user';
        }
        if ($this->getWsRoleSubscribeGroup() === true) {
            $actionRestriction[] = 'workspace-role-subscribe_group';
        }
        if ($this->getWsRoleUnsubscribeUser() === true) {
            $actionRestriction[] = 'workspace-role-unsubscribe_user';
        }
        if ($this->getWsRoleUnsubscribeGroup() === true) {
            $actionRestriction[] = 'workspace-role-unsubscribe_group';
        }
        if ($this->getWsRoleChangeRight() === true) {
            $actionRestriction[] = 'workspace-role-change_right';
        }
        if ($this->getWsRoleCreate() === true) {
            $actionRestriction[] = 'workspace-role-create';
        }
        if ($this->getWsRoleDelete() === true) {
            $actionRestriction[] = 'workspace-role-delete';
        }
        if ($this->getWsRoleUpdate() === true) {
            $actionRestriction[] = 'workspace-role-update';
        }

        return $actionRestriction;
    }

    public function hasNoRestriction()
    {
        return $this->getResourceCopy() === true
            and $this->getResourceCreate() === true
            and $this->getResourceShortcut() === true
            and $this->getResourceRead() === true
            and $this->getWsToolRead() === true
            and $this->getResourceExport() === true
            and $this->getResourceUpdate() === true
            and $this->getResourceUpdateRename() === true
            and $this->getResourceChildUpdate() === true
            and $this->getResourceDelete() === true
            and $this->getResourceMove() === true
            and $this->getWsRoleSubscribeUser() === true
            and $this->getWsRoleSubscribeGroup() === true
            and $this->getWsRoleUnsubscribeUser() === true
            and $this->getWsRoleUnsubscribeGroup() === true
            and $this->getWsRoleChangeRight() === true
            and $this->getWsRoleCreate() === true
            and $this->getWsRoleDelete() === true
            and $this->getWsRoleUpdate() === true;
    }

    public function hasAllRestriction()
    {
        return count($this->getActionRestriction()) === 0;
    }

    public function copy (LogWorkspaceWidgetConfig $source = null)
    {
        if ($source !== null) {
            $this->setResourceCopy($source->getResourceCopy());
            $this->setResourceCreate($source->getResourceCreate());
            $this->setResourceShortcut($source->getResourceShortcut());
            $this->setResourceRead($source->getResourceRead());
            $this->setWsToolRead($source->getWsToolRead());
            $this->setResourceExport($source->getResourceExport());
            $this->setResourceUpdate($source->getResourceUpdate());
            $this->setResourceUpdateRename($source->getResourceUpdateRename());
            $this->setResourceChildUpdate($source->getResourceChildUpdate());
            $this->setResourceDelete($source->getResourceDelete());
            $this->setResourceMove($source->getResourceMove());
            $this->setWsRoleSubscribeUser($source->getWsRoleSubscribeUser());
            $this->setWsRoleSubscribeGroup($source->getWsRoleSubscribeGroup());
            $this->setWsRoleUnsubscribeUser($source->getWsRoleUnsubscribeUser());
            $this->setWsRoleUnsubscribeGroup($source->getWsRoleUnsubscribeGroup());
            $this->setWsRoleChangeRight($source->getWsRoleChangeRight());
            $this->setWsRoleCreate($source->getWsRoleCreate());
            $this->setWsRoleDelete($source->getWsRoleDelete());
            $this->setWsRoleUpdate($source->getWsRoleUpdate());

            $this->setAmount($source->getAmount());
            $this->setWorkspace($source->getWorkspace());
        }
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
     * Set isDefault
     *
     * @param  boolean                  $isDefault
     * @return LogWorkspaceWidgetConfig
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set amount
     *
     * @param  integer                  $amount
     * @return LogWorkspaceWidgetConfig
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set resourceCopy
     *
     * @param  boolean                  $resourceCopy
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceCopy($resourceCopy)
    {
        $this->resourceCopy = $resourceCopy;

        return $this;
    }

    /**
     * Get resourceCopy
     *
     * @return boolean
     */
    public function getResourceCopy()
    {
        return $this->resourceCopy;
    }

    /**
     * Set resourceCreate
     *
     * @param  boolean                  $resourceCreate
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceCreate($resourceCreate)
    {
        $this->resourceCreate = $resourceCreate;

        return $this;
    }

    /**
     * Get resourceCreate
     *
     * @return boolean
     */
    public function getResourceCreate()
    {
        return $this->resourceCreate;
    }

    /**
     * Set resourceShortcut
     *
     * @param  boolean                  $resourceShortcut
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceShortcut($resourceShortcut)
    {
        $this->resourceShortcut = $resourceShortcut;

        return $this;
    }

    /**
     * Get resourceShortcut
     *
     * @return boolean
     */
    public function getResourceShortcut()
    {
        return $this->resourceShortcut;
    }

    /**
     * Set resourceRead
     *
     * @param  boolean                  $resourceRead
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceRead($resourceRead)
    {
        $this->resourceRead = $resourceRead;

        return $this;
    }

    /**
     * Get resourceRead
     *
     * @return boolean
     */
    public function getResourceRead()
    {
        return $this->resourceRead;
    }

    /**
     * Set wsToolRead
     *
     * @param  boolean                  $wsToolRead
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsToolRead($wsToolRead)
    {
        $this->wsToolRead = $wsToolRead;

        return $this;
    }

    /**
     * Get wsToolRead
     *
     * @return boolean
     */
    public function getWsToolRead()
    {
        return $this->wsToolRead;
    }

    /**
     * Set resourceExport
     *
     * @param  boolean                  $resourceExport
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceExport($resourceExport)
    {
        $this->resourceExport = $resourceExport;

        return $this;
    }

    /**
     * Get resourceExport
     *
     * @return boolean
     */
    public function getResourceExport()
    {
        return $this->resourceExport;
    }

    /**
     * Set resourceUpdate
     *
     * @param  boolean                  $resourceUpdate
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceUpdate($resourceUpdate)
    {
        $this->resourceUpdate = $resourceUpdate;

        return $this;
    }

    /**
     * Get resourceUpdate
     *
     * @return boolean
     */
    public function getResourceUpdate()
    {
        return $this->resourceUpdate;
    }

    /**
     * Set resourceUpdateRename
     *
     * @param  boolean                  $resourceUpdateRename
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceUpdateRename($resourceUpdateRename)
    {
        $this->resourceUpdateRename = $resourceUpdateRename;

        return $this;
    }

    /**
     * Get resourceUpdateRename
     *
     * @return boolean
     */
    public function getResourceUpdateRename()
    {
        return $this->resourceUpdateRename;
    }

    /**
     * Set resourceChildUpdate
     *
     * @param  boolean                  $resourceChildUpdate
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceChildUpdate($resourceChildUpdate)
    {
        $this->resourceChildUpdate = $resourceChildUpdate;

        return $this;
    }

    /**
     * Get resourceChildUpdate
     *
     * @return boolean
     */
    public function getResourceChildUpdate()
    {
        return $this->resourceChildUpdate;
    }

    /**
     * Set resourceDelete
     *
     * @param  boolean                  $resourceDelete
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceDelete($resourceDelete)
    {
        $this->resourceDelete = $resourceDelete;

        return $this;
    }

    /**
     * Get resourceDelete
     *
     * @return boolean
     */
    public function getResourceDelete()
    {
        return $this->resourceDelete;
    }

    /**
     * Set resourceMove
     *
     * @param  boolean                  $resourceMove
     * @return LogWorkspaceWidgetConfig
     */
    public function setResourceMove($resourceMove)
    {
        $this->resourceMove = $resourceMove;

        return $this;
    }

    /**
     * Get resourceMove
     *
     * @return boolean
     */
    public function getResourceMove()
    {
        return $this->resourceMove;
    }

    /**
     * Set wsRoleSubscribeUser
     *
     * @param  boolean                  $wsRoleSubscribeUser
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleSubscribeUser($wsRoleSubscribeUser)
    {
        $this->wsRoleSubscribeUser = $wsRoleSubscribeUser;

        return $this;
    }

    /**
     * Get wsRoleSubscribeUser
     *
     * @return boolean
     */
    public function getWsRoleSubscribeUser()
    {
        return $this->wsRoleSubscribeUser;
    }

    /**
     * Set wsRoleSubscribeGroup
     *
     * @param  boolean                  $wsRoleSubscribeGroup
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleSubscribeGroup($wsRoleSubscribeGroup)
    {
        $this->wsRoleSubscribeGroup = $wsRoleSubscribeGroup;

        return $this;
    }

    /**
     * Get wsRoleSubscribeGroup
     *
     * @return boolean
     */
    public function getWsRoleSubscribeGroup()
    {
        return $this->wsRoleSubscribeGroup;
    }

    /**
     * Set wsRoleUnsubscribeUser
     *
     * @param  boolean                  $wsRoleUnsubscribeUser
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleUnsubscribeUser($wsRoleUnsubscribeUser)
    {
        $this->wsRoleUnsubscribeUser = $wsRoleUnsubscribeUser;

        return $this;
    }

    /**
     * Get wsRoleUnsubscribeUser
     *
     * @return boolean
     */
    public function getWsRoleUnsubscribeUser()
    {
        return $this->wsRoleUnsubscribeUser;
    }

    /**
     * Set wsRoleUnsubscribeGroup
     *
     * @param  boolean                  $wsRoleUnsubscribeGroup
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleUnsubscribeGroup($wsRoleUnsubscribeGroup)
    {
        $this->wsRoleUnsubscribeGroup = $wsRoleUnsubscribeGroup;

        return $this;
    }

    /**
     * Get wsRoleUnsubscribeGroup
     *
     * @return boolean
     */
    public function getWsRoleUnsubscribeGroup()
    {
        return $this->wsRoleUnsubscribeGroup;
    }

    /**
     * Set wsRoleChangeRight
     *
     * @param  boolean                  $wsRoleChangeRight
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleChangeRight($wsRoleChangeRight)
    {
        $this->wsRoleChangeRight = $wsRoleChangeRight;

        return $this;
    }

    /**
     * Get wsRoleChangeRight
     *
     * @return boolean
     */
    public function getWsRoleChangeRight()
    {
        return $this->wsRoleChangeRight;
    }

    /**
     * Set wsRoleCreate
     *
     * @param  boolean                  $wsRoleCreate
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleCreate($wsRoleCreate)
    {
        $this->wsRoleCreate = $wsRoleCreate;

        return $this;
    }

    /**
     * Get wsRoleCreate
     *
     * @return boolean
     */
    public function getWsRoleCreate()
    {
        return $this->wsRoleCreate;
    }

    /**
     * Set wsRoleDelete
     *
     * @param  boolean                  $wsRoleDelete
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleDelete($wsRoleDelete)
    {
        $this->wsRoleDelete = $wsRoleDelete;

        return $this;
    }

    /**
     * Get wsRoleDelete
     *
     * @return boolean
     */
    public function getWsRoleDelete()
    {
        return $this->wsRoleDelete;
    }

    /**
     * Set wsRoleUpdate
     *
     * @param  boolean                  $wsRoleUpdate
     * @return LogWorkspaceWidgetConfig
     */
    public function setWsRoleUpdate($wsRoleUpdate)
    {
        $this->wsRoleUpdate = $wsRoleUpdate;

        return $this;
    }

    /**
     * Get wsRoleUpdate
     *
     * @return boolean
     */
    public function getWsRoleUpdate()
    {
        return $this->wsRoleUpdate;
    }

    /**
     * Set workspace
     *
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return LogWorkspaceWidgetConfig
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
}
