<?php

namespace Claroline\CoreBundle\Event\Log;

class LogGroupCreateEvent extends LogGenericEvent
{
    const ACTION = 'group-create';

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

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
