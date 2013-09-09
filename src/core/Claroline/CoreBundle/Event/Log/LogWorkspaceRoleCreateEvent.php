<?php

namespace Claroline\CoreBundle\Event\Log;

class LogWorkspaceRoleCreateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-role-create';

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
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
