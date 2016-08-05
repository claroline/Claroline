<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Workspace\Workspace;

class LogHomeTabWorkspaceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'workspace-home-tab-delete';

    /**
     * Constructor.
     */
    public function __construct(Workspace $workspace, array $details)
    {
        parent::__construct(
            self::ACTION,
            $details,
            null,
            null,
            null,
            null,
            $workspace
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
