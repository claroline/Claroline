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
use Claroline\CursusBundle\Entity\CursusGroup;

class LogCursusGroupRegistrationEvent extends LogGenericEvent
{
    const ACTION = 'cursus-group-registration';

    public function __construct(CursusGroup $cursusGroup)
    {
        $group = $cursusGroup->getGroup();
        $cursus = $cursusGroup->getCursus();

        $details = [];
        $details['groupName'] = $group->getName();
        $details['cursusId'] = $cursus->getUuid();
        $details['cursusTitle'] = $cursus->getTitle();
        $details['cursusCode'] = $cursus->getCode();
        $details['type'] = $cursusGroup->getGroupType();
        $details['registrationDate'] = $cursusGroup->getRegistrationDate();

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
