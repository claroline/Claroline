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
use Claroline\CursusBundle\Entity\Registration\SessionUser;

class LogSubscriptionSetStatusEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-subscription-set-status';

    public function __construct(SessionUser $sessionUser)
    {
        $session = $sessionUser->getSession();
        $course = $session->getCourse();
        $user = $sessionUser->getUser();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['sessionId'] = $session->getUuid();
        $details['sessionName'] = $session->getName();
        $details['sessionStartDate'] = $session->getStartDate()->format('d/m/Y');
        $details['type'] = $sessionUser->getType();
        $details['courseId'] = $course->getUuid();
        $details['courseTitle'] = $course->getName();
        $details['courseCode'] = $course->getCode();
        $details['date'] = (new \DateTime())->format('d/m/Y H:i:s');

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
