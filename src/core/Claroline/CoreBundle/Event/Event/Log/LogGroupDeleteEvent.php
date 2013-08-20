<?php

namespace Claroline\CoreBundle\Event\Event\Log;

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
