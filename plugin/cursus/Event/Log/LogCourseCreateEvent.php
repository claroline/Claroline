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
use Claroline\CursusBundle\Entity\Course;

class LogCourseCreateEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-course-create';

    public function __construct(Course $course)
    {
        $details = [];
        $details['id'] = $course->getId();
        $details['title'] = $course->getTitle();
        $details['code'] = $course->getCode();
        $details['publicRegistration'] = $course->getPublicRegistration();
        $details['publicUnregistration'] = $course->getPublicUnregistration();
        $details['registrationValidation'] = $course->getRegistrationValidation();
        $details['icon'] = $course->getIcon();
        $details['tutorRoleName'] = $course->getTutorRoleName();
        $details['learnerRoleName'] = $course->getLearnerRoleName();
        $details['userValidation'] = $course->getUserValidation();
        $details['organizationValidation'] = $course->getOrganizationValidation();
        $details['maxUsers'] = $course->getMaxUsers();
        $details['defaultSessionDuration'] = $course->getDefaultSessionDuration();
        $details['withSessionEvent'] = $course->getWithSessionEvent();
        $details['organizations'] = [];
        $workspace = $course->getWorkspace();
        $workspaceModel = $course->getWorkspaceModel();
        $organizations = $course->getOrganizations();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }
        if (!is_null($workspaceModel)) {
            $details['workspaceModelId'] = $workspaceModel->getId();
            $details['workspaceModelName'] = $workspaceModel->getName();
        }
        foreach ($organizations as $organization) {
            $details['organizations'][] = [
                'id' => $organization->getId(),
                'name' => $organization->getName(),
                'default' => $organization->isDefault(),
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
