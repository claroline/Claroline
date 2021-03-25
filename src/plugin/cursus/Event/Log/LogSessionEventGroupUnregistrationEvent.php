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
use Claroline\CursusBundle\Entity\Registration\EventGroup;

class LogSessionEventGroupUnregistrationEvent extends LogGenericEvent
{
    const ACTION = 'course-event-group-unregistration';

    public function __construct(EventGroup $eventGroup)
    {
        $event = $eventGroup->getEvent();
        $session = $event->getSession();
        $group = $eventGroup->getGroup();
        $course = $session->getCourse();
        $details = [];
        $details['groupName'] = $group->getName();
        $details['sessionEventId'] = $event->getUuid();
        $details['sessionEventName'] = $event->getName();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getName();
        $details['courseCode'] = $course->getCode();
        $details['registrationDate'] = $eventGroup->getDate()->format('d/m/Y H:i:s');
        $details['type'] = $eventGroup->getType();

        parent::__construct(
            self::ACTION,
            $details,
            null,
            $group
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
