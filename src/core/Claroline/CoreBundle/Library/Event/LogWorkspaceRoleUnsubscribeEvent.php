<?php

namespace Claroline\CoreBundle\Library\Event;

class LogWorkspaceRoleUnsubscribeEvent extends LogGenericEvent
{
    const ACTION_USER = 'ws_role_unsubscribe_user';
    const ACTION_GROUP = 'ws_role_unsubscribe_group';

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
            $details['receiverUser'] = array(
                'firstName' => $receiver->getFirstName(),
                'lastName' => $receiver->getLastName()
            );
        }

        if ($receiverGroup !== null) {
            $details['receiverGroup'] = array(
                'name' => $receiverGroup->getName()
            );
        }

        $action = self::ACTION_USER;
        if ($receiverGroup != null) {
            $action = self::ACTION_GROUP;
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