<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserUpdateEvent extends LogGenericEvent
{
    const action = 'user_update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * ('propertyName1' => ['property old value 1', 'property new value 1'], 'propertyName2' => ['property old value 2', 'property new value 2'] etc.)
     * 
     * Please respect lower caml case naming convention for property names
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