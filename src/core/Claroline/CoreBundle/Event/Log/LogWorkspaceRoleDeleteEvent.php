<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceRoleDeleteEvent extends LogGenericEvent
{
    const ACTION = 'workspace-role-delete';

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

        $this->setIsDisplayedInWorkspace(true);
    }
}
