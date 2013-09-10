<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Event\MandatoryEventInterface;

class LogGroupUpdateEvent extends LogGenericEvent implements MandatoryEventInterface
{
    const ACTION = 'group-update';

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

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
