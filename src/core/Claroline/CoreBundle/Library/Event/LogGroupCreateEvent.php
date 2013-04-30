<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupCreateEvent extends LogGenericEvent
{
    const ACTION = 'group_create';

    /**
     * Constructor.
     */
    public function __construct($receiverGroup)
    {
        parent::__construct(
            self::ACTION,
            array(
                'receiverGroup' => array(
                    'name' => $receiverGroup->getName()
                )
            ),
            null,
            $receiverGroup
        );
    }
}