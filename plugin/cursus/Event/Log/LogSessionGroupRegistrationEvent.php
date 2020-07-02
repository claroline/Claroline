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
use Claroline\CursusBundle\Entity\CourseSessionGroup;

class LogSessionGroupRegistrationEvent extends LogGenericEvent
{
    const ACTION = 'course-session-group-registration';

    public function __construct(CourseSessionGroup $sessionGroup)
    {
        $session = $sessionGroup->getSession();
        $group = $sessionGroup->getGroup();
        $course = $session->getCourse();
        $details = [];
        $details['groupName'] = $group->getName();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['sessionCreationDate'] = $session->getCreationDate()->format('d/m/Y H:i:s');
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $details['registrationDate'] = $sessionGroup->getRegistrationDate()->format('d/m/Y H:i:s');
        $details['type'] = $sessionGroup->getGroupType();

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
