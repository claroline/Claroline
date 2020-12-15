<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Event\Log;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\ScormBundle\Entity\Scorm;

class LogScormResultEvent extends LogGenericEvent
{
    const ACTION = 'resource-scorm-sco_result';

    public function __construct(Scorm $scorm, User $user, array $details)
    {
        parent::__construct(
            self::ACTION,
            $details,
            $user,
            null,
            $scorm->getResourceNode(),
            null,
            $scorm->getResourceNode()->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
