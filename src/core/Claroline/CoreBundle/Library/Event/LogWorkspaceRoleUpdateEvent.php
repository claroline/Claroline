<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleUpdateEvent extends LogGenericEvent
{
    const action = 'workspace_role_update';

    /**
     * Constructor.
     * OldValues and newValues expected variables are arrays which contain all modified properties, in the following form:
     * ('property_name_1' => 'property_value_1', 'property_name_2' => 'property_value_2' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($role, $oldValues, $newValues)
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
                    'old_values' => $oldValues,
                    'new_values' => $newValues
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