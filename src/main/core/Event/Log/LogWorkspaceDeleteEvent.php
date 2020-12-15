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

class LogWorkspaceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'workspace-delete';

    /**
     * Constructor.
     */
    public function __construct($workspace)
    {
        parent::__construct(
            self::ACTION,
            array(
                'workspace' => array(
                    'name' => $workspace->getName(),
                ),
            ),
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
        return array(self::DISPLAYED_ADMIN);
    }
}
