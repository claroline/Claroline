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
use Claroline\CursusBundle\Entity\Session;

class LogSessionCreateEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-course-session-create';

    public function __construct(Session $session)
    {
        $course = $session->getCourse();
        $workspace = $session->getWorkspace();
        $learnerRole = $session->getLearnerRole();
        $tutorRole = $session->getTutorRole();
        $details = [];
        $details['id'] = $session->getUuid();
        $details['name'] = $session->getName();
        $details['defaultSession'] = $session->isDefaultSession();
        $details['publicRegistration'] = $session->getPublicRegistration();
        $details['publicUnregistration'] = $session->getPublicUnregistration();
        $details['registrationValidation'] = $session->getRegistrationValidation();
        $details['startDate'] = $session->getStartDate();
        $details['endDate'] = $session->getEndDate();
        $details['quotaDays'] = $session->getQuotaDays();
        $details['userValidation'] = $session->getUserValidation();
        $details['maxUsers'] = $session->getMaxUsers();

        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getName();
        $details['courseCode'] = $course->getCode();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getUuid();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getUuid();
        }

        if (!is_null($learnerRole)) {
            $details['learnerRoleId'] = $learnerRole->getUuid();
            $details['learnerRoleName'] = $learnerRole->getName();
            $details['learnerRoleKey'] = $learnerRole->getTranslationKey();
        }

        if (!is_null($tutorRole)) {
            $details['tutorRoleId'] = $tutorRole->getUuid();
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
