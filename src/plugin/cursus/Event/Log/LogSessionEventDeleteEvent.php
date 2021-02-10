<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CursusBundle\Entity\Event;

class LogSessionEventDeleteEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-session-event-delete';

    public function __construct(Event $sessionEvent)
    {
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $details = [];
        $details['id'] = $sessionEvent->getUuid();
        $details['name'] = $sessionEvent->getName();
        $details['startDate'] = $sessionEvent->getStartDate();
        $details['endDate'] = $sessionEvent->getEndDate();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getName();
        $details['courseCode'] = $course->getCode();
        $details['tutors'] = [];

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
