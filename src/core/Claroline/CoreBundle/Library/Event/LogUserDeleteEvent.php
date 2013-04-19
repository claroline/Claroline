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
                'receiverUser' => array(
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName()
                )
            ),
            $receiver
        );
    }
}