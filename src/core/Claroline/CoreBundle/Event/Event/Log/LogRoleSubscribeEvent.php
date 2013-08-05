<?php

namespace Claroline\CoreBundle\Event\Event\Log;

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class LogRoleSubscribeEvent extends LogGenericEvent
{
    const ACTION_USER = 'ws_role_subscribe_user';
    const ACTION_GROUP = 'ws_role_subscribe_group';

    /**
     * Constructor.
     */
    public function __construct(Role $role, AbstractRoleSubject $subject, AbstractWorkspace $workspace = null)
    {
        $receiver = null;
        $receiverGroup = null;

        $details = array('role' => array('name' => $role->getTranslationKey()));

        if ($workspace) {
            $details['workspace'] = array('name' => $role->getWorkspace()->getName());
        }

        if ($subject instanceof \Claroline\CoreBundle\Entity\User) {
            $details['receiverUser'] = array(
                'firstName' => $subject->getFirstName(),
                'lastName' => $subject->getLastName()
            );
            $action = self::ACTION_USER;
            $receiver = $subject;
        } else {
          $details['receiverGroup'] = array(
                'name' => $subject->getName()
            );
           $action = self::ACTION_GROUP;
           $receiverGroup = $subject;
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
