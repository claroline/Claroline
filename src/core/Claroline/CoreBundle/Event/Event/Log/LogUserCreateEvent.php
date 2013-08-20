<?php

namespace Claroline\CoreBundle\Event\Event\Log;

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
                'receiverUser' => array(
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName()
                )
            ),
            $receiver
        );
    }
}
