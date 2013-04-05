<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserCreateEvent extends LogGenericEvent
{
    const action = 'user_create';

    /**
     * Constructor.
     */
    public function __construct($receiver)
    {
        parent::__construct(
            self::action,
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