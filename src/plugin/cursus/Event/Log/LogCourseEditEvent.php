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

class LogCourseEditEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-course-edit';

    public function __construct(Course $course)
    {
        $details = [];
        $details['id'] = $course->getUuid();
        $details['title'] = $course->getName();
        $details['code'] = $course->getCode();
        $details['publicRegistration'] = $course->getPublicRegistration();
        $details['publicUnregistration'] = $course->getPublicUnregistration();
        $details['registrationValidation'] = $course->getRegistrationValidation();
        $details['userValidation'] = $course->getUserValidation();
        $details['maxUsers'] = $course->getMaxUsers();
        $details['defaultSessionDuration'] = $course->getDefaultSessionDuration();
        $details['organizations'] = [];
        $workspace = $course->getWorkspace();
        $organizations = $course->getOrganizations()->toArray();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getUuid();
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
