<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleCreateEvent extends LogGenericEvent
{
    const action = 'ws_role_create';

    /**
     * Constructor.
     */
    public function __construct($role)
    {
        parent::__construct(
            self::action,
            array(
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
            $role->getWorkspace()
        );
    }
}