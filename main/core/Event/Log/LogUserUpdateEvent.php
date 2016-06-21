<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Event\MandatoryEventInterface;

class LogUserUpdateEvent extends LogGenericEvent implements MandatoryEventInterface
{
    const ACTION = 'user-update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * ).
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
                    'changeSet' => $changeSet,
                ),
            ),
            $receiver
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
