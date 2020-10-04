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

class LogSessionQueueValidatorValidateEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-session-queue-validator-validate';

    public function __construct(CourseSessionRegistrationQueue $queue)
    {
        $session = $queue->getSession();
        $course = $session->getCourse();
        $user = $queue->getUser();
        $validator = $queue->getValidator();
        $details = [];
        $details['status'] = $queue->getStatus();
        $details['userId'] = $user->getUuid();
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getName();
        $details['courseCode'] = $course->getCode();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['validatorId'] = $validator->getUuid();
        $details['validatorUsername'] = $validator->getUsername();
        $details['validatorFirsName'] = $validator->getFirstName();
        $details['validatorLastName'] = $validator->getLastName();
        //$details['validatorValidationDate'] = $queue->getValidatorValidationDate();

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
