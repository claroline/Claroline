<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupRemoveUserEvent extends LogGenericEvent
{
    const ACTION = 'group_remove_user';

    public function __construct($receiverGroup, $receiver)
    {
        parent::__construct(
            self::ACTION,
            array(
                'receiverUser' => array(
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName()
                ),
                'receiverGroup' => array(
                    'name' => $receiverGroup->getName()
                )
            ),
            $receiver,
            $receiverGroup
        );
    }
}