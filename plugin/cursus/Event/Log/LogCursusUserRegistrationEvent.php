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
use Claroline\CursusBundle\Entity\CursusUser;

class LogCursusUserRegistrationEvent extends LogGenericEvent
{
    const ACTION = 'cursus-user-registration';

    public function __construct(CursusUser $cursusUser)
    {
        $user = $cursusUser->getUser();
        $cursus = $cursusUser->getCursus();

        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['cursusId'] = $cursus->getUuid();
        $details['cursusTitle'] = $cursus->getTitle();
        $details['cursusCode'] = $cursus->getCode();
        $details['type'] = $cursusUser->getUserType();
        $details['registrationDate'] = $cursusUser->getRegistrationDate();

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
