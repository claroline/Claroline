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

class LogSessionEventUserUnregistrationEvent extends LogGenericEvent
{
    const ACTION = 'session-event-user-unregistration';

    public function __construct(SessionEventUser $sessionEventUser)
    {
        $sessionEvent = $sessionEventUser->getSessionEvent();
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $user = $sessionEventUser->getUser();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['sessionEventId'] = $sessionEvent->getId();
        $details['sessionEventName'] = $sessionEvent->getName();
        $details['sessionId'] = $session->getId();
        $details['sessionName'] = $session->getName();
        $details['sessionCreationDate'] = $session->getCreationDate()->format('d/m/Y H:i:s');
        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $registrationDate = $sessionEventUser->getRegistrationDate();

        if (!is_null($registrationDate)) {
            $details['registrationDate'] = $registrationDate->format('d/m/Y H:i:s');
        }

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
