<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupDeleteEvent extends LogGenericEvent
{
    const action = 'group_delete';

    /**
     * Constructor.
     */
    public function __construct($receiverGroup)
    {
        parent::__construct(
            self::action,
            array(
                'receiver_group' => array(
                    'name' => $receiverGroup->getName()
                )
            ),
            null,
            $receiverGroup
        );
    }
}