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
use Claroline\ScormBundle\Entity\Scorm2004Resource;

class LogScorm2004ResultEvent extends LogGenericEvent
{
    const ACTION = 'resource-scorm_2004-sco_result';

    public function __construct(Scorm2004Resource $scormResource, User $user, array $details)
    {
        parent::__construct(
            self::ACTION,
            $details,
            $user,
            null,
            $scormResource->getResourceNode(),
            null,
            $scormResource->getResourceNode()->getWorkspace()
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
