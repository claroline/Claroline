<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserUpdateEvent extends LogGenericEvent
{
    const action = 'user_update';

    /**
     * Constructor.
     * OldValues and newValues expected variables are arrays which contain all modified properties, in the following form:
     * ('property_name_1' => 'property_value_1', 'property_name_2' => 'property_value_2' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($receiver, $changeSet)
    {
        parent::__construct(
            self::action,
            array(
                'receiver_user' => array(
                    'first_name' => $receiver->getFirstName(),
                    'last_name' => $receiver->getLastName(),
                    'change_set' => $changeSet
                )
            ),
            $receiver
        );
    }
}