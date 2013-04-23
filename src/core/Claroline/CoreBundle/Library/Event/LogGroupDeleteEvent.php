<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupDeleteEvent extends LogGenericEvent
{
    const ACTION = 'group_delete';

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