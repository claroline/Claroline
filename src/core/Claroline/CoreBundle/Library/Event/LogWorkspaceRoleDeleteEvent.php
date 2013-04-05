<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleDeleteEvent extends LogGenericEvent
{
    const action = 'workspace_role_delete';

    /**
     * Constructor.
     */
    public function __construct($role)
    {
        parent::__construct(
            self::action,
            array(
                'owner' => array(
                    'last_name' => $role->getWorkspace()->getCreator()->getLastName(),
                    'first_name' => $role->getWorkspace()->getCreator()->getFirstName()
                ),
                'role' => array(
                    'name' => $role->getName()
                ),
                'workspace' => array(
                    'name' => $role->getWorkspace()->getName()
                )
            ),
            null,
            null,
            null,
            $role,
            $role->getWorkspace(),
            $role->getWorkspace()->getCreator()
        );
    }
}