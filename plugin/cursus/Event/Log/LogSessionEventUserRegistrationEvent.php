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
use Claroline\CursusBundle\Entity\SessionEventUser;

class LogSessionEventUserRegistrationEvent extends LogGenericEvent
{
    const ACTION = 'session-event-user-registration';

    public function __construct(SessionEventUser $sessionEventUser)
    {
        $user = $sessionEventUser->getUser();
        $sessionEvent = $sessionEventUser->getSessionEvent();
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['sessionEventId'] = $sessionEvent->getUuid();
        $details['sessionEventName'] = $sessionEvent->getName();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $details['registrationStatus'] = $sessionEventUser->getRegistrationStatus();
        $details['applicationDate'] = $sessionEventUser->getApplicationDate();
        $details['registrationDate'] = $sessionEventUser->getRegistrationDate();

        parent::__construct(
            self::ACTION,
            $details,
            $user
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
