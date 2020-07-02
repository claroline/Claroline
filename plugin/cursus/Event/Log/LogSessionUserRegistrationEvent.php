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
use Claroline\CursusBundle\Entity\CourseSessionUser;

class LogSessionUserRegistrationEvent extends LogGenericEvent
{
    const ACTION = 'course-session-user-registration';

    public function __construct(CourseSessionUser $sessionUser)
    {
        $session = $sessionUser->getSession();
        $user = $sessionUser->getUser();
        $course = $session->getCourse();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['sessionCreationDate'] = $session->getCreationDate()->format('d/m/Y H:i:s');
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $details['registrationDate'] = $sessionUser->getRegistrationDate()->format('d/m/Y H:i:s');
        $details['type'] = $sessionUser->getUserType();

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
