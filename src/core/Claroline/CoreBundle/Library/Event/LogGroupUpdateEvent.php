<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupUpdateEvent extends LogGenericEvent
{
    const action = 'group_update';

    /**
     * Constructor.
     * OldValues and newValues expected variables are arrays which contain all modified properties, in the following form:
     * ('property_name_1' => 'property_value_1', 'property_name_2' => 'property_value_2' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($receiverGroup, $oldValues, $newValues)
    {
        parent::__construct(
            self::action,
            array(
                'receiver_group' => array(
                    'name' => $receiverGroup->getName(),
                    'old_values' => $oldValues,
                    'new_values' => $newValues
                )
            ),
            null,
            $receiverGroup
        );
    }
}