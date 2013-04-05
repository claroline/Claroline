<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleSubscribeEvent extends LogGenericEvent
{
    const action = 'workspace_role_subscribe';

    /**
     * Constructor.
     */
    public function __construct($role, $receiver=null, $receiverGroup=null)
    {
        $details = array(
            'owner' => array(
                'last_name' => $role->getWorkspace()->getCreator()->getLastName(),
                'first_name' => $role->getWorkspace()->getCreator()->getFirstName()
            ),
            'role' => array(
                'name' => $role->getName()
            ),
            'workspace' => array(
                'name' => $role->getWorkspace()->getName()
            ),
        )

        if ($receiver !== null) {
            $details['receiver_user'] = array(
                'first_name' => $receiver->getFirstName(),
                'last_name' => $receiver->getLastName()
            );
        }

        if ($receiverGroup !== null) {
            $details['receiver_group'] = array(
                'name' => $receiverGroup->getName()
            );
        }

        parent::__construct(
            self::action,
            $details,   
            $receiver,
            $receiverGroup,
            null,
            $role,
            $role->getWorkspace(),
            $role->getWorkspace()->getCreator()
        );
    }
}