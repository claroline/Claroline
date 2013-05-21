<?php

namespace Claroline\CoreBundle\Library\Event;

class LogGroupUpdateEvent extends LogGenericEvent
{
    const ACTION = 'group_update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * )
     * 
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($receiverGroup, $changeSet)
    {
        parent::__construct(
            self::ACTION,
            array(
                'receiverGroup' => array(
                    'name' => $receiverGroup->getName(),
                    'changeSet' => $changeSet
                )
            ),
            null,
            $receiverGroup
        );
    }
}