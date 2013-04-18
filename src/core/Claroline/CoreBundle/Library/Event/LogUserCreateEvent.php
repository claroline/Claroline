<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserCreateEvent extends LogGenericEvent
{
    const ACTION = 'user_create';

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