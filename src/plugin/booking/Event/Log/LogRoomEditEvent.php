<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Event\Log;

use Claroline\BookingBundle\Entity\Room;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogRoomEditEvent extends LogGenericEvent
{
    const ACTION = 'bookingbundle-room-edit';

    public function __construct(Room $room)
    {
        $details = [];
        $details['id'] = $room->getUuid();
        $details['title'] = $room->getName();
        $details['code'] = $room->getCode();
        $details['capacity'] = $room->getCapacity();

        parent::__construct(self::ACTION, $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_ADMIN];
    }
}
