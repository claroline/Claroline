<?php

namespace Claroline\CoreBundle\Event\Event\Log;

use Claroline\CoreBundle\Event\MandatoryEventInterface;

class LogUserUpdateEvent extends LogGenericEvent implements MandatoryEventInterface
{
    const ACTION = 'user_update';

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
    public function __construct($receiver, $changeSet)
    {
        parent::__construct(
            self::ACTION,
            array(
                'receiverUser' => array(
                    'firstName' => $receiver->getFirstName(),
                    'lastName' => $receiver->getLastName(),
                    'changeSet' => $changeSet
                )
            ),
            $receiver
        );
    }
}
