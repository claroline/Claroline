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
use Claroline\CursusBundle\Entity\SessionEvent;

class LogSessionEventCreateEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-session-event-create';

    public function __construct(SessionEvent $sessionEvent)
    {
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $details = [];
        $details['id'] = $sessionEvent->getId();
        $details['name'] = $sessionEvent->getName();
        $details['startDate'] = $sessionEvent->getStartDate();
        $details['endDate'] = $sessionEvent->getEndDate();
        $details['sessionId'] = $session->getId();
        $details['sessionName'] = $session->getName();
        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();

        foreach ($sessionEvent->getTutors() as $tutor) {
            $details['tutors'][] = [
                'id' => $tutor->getId(),
                'username' => $tutor->getUsername(),
                'firstName' => $tutor->getFirstName(),
                'lastName' => $tutor->getLastName(),
                'guid' => $tutor->getGuid(),
            ];
        }
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
