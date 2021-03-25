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

class LogGroupRemoveUserEvent extends LogGenericEvent
{
    const ACTION = 'group-remove_user';

    public function __construct($receiverGroup, $receiver)
    {
        parent::__construct(
            self::ACTION,
            [
                'receiverUser' => [
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName(),
                ],
                'receiverGroup' => [
                    'name' => $receiverGroup->getName(),
                ],
            ],
            $receiver,
            $receiverGroup
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_ADMIN];
    }
}
