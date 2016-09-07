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
use Claroline\CursusBundle\Entity\CourseSession;

class LogCourseSessionEditEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-course-session-edit';

    public function __construct(CourseSession $session)
    {
        $course = $session->getCourse();
        $workspace = $session->getWorkspace();
        $learnerRole = $session->getLearnerRole();
        $tutorRole = $session->getTutorRole();
        $details = [];
        $details['id'] = $session->getId();
        $details['name'] = $session->getName();
        $details['defaultSession'] = $session->isDefaultSession();
        $details['creationDate'] = $session->getCreationDate();
        $details['publicRegistration'] = $session->getPublicRegistration();
        $details['publicUnregistration'] = $session->getPublicUnregistration();
        $details['registrationValidation'] = $session->getRegistrationValidation();
        $details['startDate'] = $session->getStartDate();
        $details['endDate'] = $session->getEndDate();
        $details['extra'] = $session->getExtra();
        $details['userValidation'] = $session->getUserValidation();
        $details['organizationValidation'] = $session->getOrganizationValidation();
        $details['maxUsers'] = $session->getMaxUsers();
        $details['type'] = $session->getType();

        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }

        if (!is_null($learnerRole)) {
            $details['learnerRoleId'] = $learnerRole->getId();
            $details['learnerRoleName'] = $learnerRole->getName();
            $details['learnerRoleKey'] = $learnerRole->getTranslationKey();
        }

        if (!is_null($tutorRole)) {
            $details['tutorRoleId'] = $tutorRole->getId();
            $details['tutorRoleName'] = $tutorRole->getName();
            $details['tutorRoleKey'] = $tutorRole->getTranslationKey();
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
