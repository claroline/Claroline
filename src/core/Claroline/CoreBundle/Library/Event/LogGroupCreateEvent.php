<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupCreateEvent extends LogGenericEvent
{
    const action = 'group_create';

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
            null,
            $receiverGroup
        );
    }
}