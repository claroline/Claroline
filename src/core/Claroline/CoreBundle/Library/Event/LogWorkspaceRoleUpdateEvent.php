<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleUpdateEvent extends LogGenericEvent
{
    const ACTION = 'ws_role_update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * )
     * 
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($role, $changeSet)
    {
        parent::__construct(
            self::ACTION,
            array(
                'role' => array(
                    'name' => $role->getName(),
                    'changeSet' => $changeSet
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