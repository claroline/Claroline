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

class LogCourseSessionUserUnregistrationEvent extends LogGenericEvent
{
    const ACTION = 'course-session-user-unregistration';

    public function __construct(CourseSessionUser $sessionUser)
    {
        $session = $sessionUser->getSession();
        $course = $session->getCourse();
        $user = $sessionUser->getUser();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['sessionId'] = $session->getId();
        $details['sessionName'] = $session->getName();
        $details['sessionCreationDate'] = $session->getCreationDate()->format('d/m/Y H:i:s');
        $details['userType'] = $sessionUser->getUserType();
        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $details['registrationDate'] = $sessionUser->getRegistrationDate()->format('d/m/Y H:i:s');

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
