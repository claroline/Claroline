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

class LogCursusUserUnregistrationEvent extends LogGenericEvent
{
    const ACTION = 'cursus-user-unregistration';

    public function __construct(CursusUser $cursusUser)
    {
        $cursus = $cursusUser->getCursus();
        $user = $cursusUser->getUser();
        $details = [];
        $details['username'] = $user->getUsername();
        $details['firsName'] = $user->getFirstName();
        $details['lastName'] = $user->getLastName();
        $details['cursusId'] = $cursus->getId();
        $details['cursusTitle'] = $cursus->getTitle();
        $details['cursusCode'] = $cursus->getCode();
        $details['registrationDate'] = $cursusUser->getRegistrationDate()->format('d/m/Y H:i:s');

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
