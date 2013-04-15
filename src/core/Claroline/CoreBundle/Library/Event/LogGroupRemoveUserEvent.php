<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupRemoveUserEvent extends LogGenericEvent
{
    const action = 'group_remove_user';

    /**
     * Constructor.
     * OldValues and newValues expected variables are arrays which contain all modified properties, in the following form:
     * ('property_name_1' => 'property_value_1', 'property_name_2' => 'property_value_2' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($receiverGroup, $receiver)
    {
        parent::__construct(
            self::action,
            array(
                'receiver_user' => array(
                    'last_name' => $receiver->getLastName(),
                    'first_name' => $receiver->getFirstName()
                ),
                'receiver_group' => array(
                    'name' => $receiverGroup->getName()
                )
            ),
            $receiver,
            $receiverGroup
        );
    }
}