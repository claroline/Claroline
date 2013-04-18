<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserDeleteEvent extends LogGenericEvent
{
    const ACTION = 'user_delete';

    /**
     * Constructor.
     */
    public function __construct($receiver)
    {
        parent::__construct(
            self::ACTION,
            array(
                'receiver_user' => array(
                    'last_name' => $receiver->getLastName(),
                    'first_name' => $receiver->getFirstName()
                )
            ),
            $receiver
        );
    }
}