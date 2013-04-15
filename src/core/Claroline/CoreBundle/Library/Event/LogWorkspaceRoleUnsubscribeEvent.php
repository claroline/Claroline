<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleUnsubscribeEvent extends LogGenericEvent
{
    const action_user = 'ws_role_unsubscribe_user';
    const action_group = 'ws_role_unsubscribe_group';

    /**
     * Constructor.
     */
    public function __construct($role, $receiver=null, $receiverGroup=null)
    {
        $details = array(
            'role' => array(
                'name' => $role->getTranslationKey()
            ),
            'workspace' => array(
                'name' => $role->getWorkspace()->getName()
            )
        );

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

        $action = self::action_user;
        if ($receiverGroup != null) {
            $action = self::action_group;
        }
        parent::__construct(
            $action,
            $details,   
            $receiver,
            $receiverGroup,
            null,
            $role,
            $role->getWorkspace()
        );
    }
}