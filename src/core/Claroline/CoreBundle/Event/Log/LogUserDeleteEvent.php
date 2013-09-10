<?php

namespace Claroline\CoreBundle\Event\Log;

class LogUserDeleteEvent extends LogGenericEvent
{
    const ACTION = 'user-delete';

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

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
