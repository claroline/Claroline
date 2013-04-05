<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleChangeRightEvent extends LogGenericEvent
{
    const action = 'workspace_role_change_right';

    /**
     * Constructor.
     * OldRights and newRights expected variables are arrays which contain all modified rights, for example:
     * ('can_delete' => 'false', 'can_copy' => 'true' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($role, $resource, $oldRights, $newRights)
    {
        parent::__construct(
            self::action,
            array(
                'owner' => array(
                    'last_name' => $role->getWorkspace()->getCreator()->getLastName(),
                    'first_name' => $role->getWorkspace()->getCreator()->getFirstName()
                ),
                'role' => array(
                    'name' => $role->getName(),
                    'old_values' => $oldRights,
                    'new_values' => $newRights
                ),
                'workspace' => array(
                    'name' => $role->getWorkspace()->getName()
                ),
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPath()
                )
            ),
            null,
            null,
            $resource,
            $role,
            $role->getWorkspace(),
            $role->getWorkspace()->getCreator()
        );
    }
}