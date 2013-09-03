<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceRoleCreateEvent extends LogGenericEvent
{
    const ACTION = 'ws_role_create';

    /**
     * Constructor.
     */
    public function __construct($role)
    {
        parent::__construct(
            self::ACTION,
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

        $this->isDisplayedInWorkspace(true);
    }
}
