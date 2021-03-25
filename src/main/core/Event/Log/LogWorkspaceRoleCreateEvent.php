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

class LogWorkspaceRoleCreateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-role-create';

    /**
     * Constructor.
     */
    public function __construct($role)
    {
        parent::__construct(
            self::ACTION,
            [
                'role' => [
                    'name' => $role->getName(),
                ],
                'workspace' => [
                    'name' => $role->getWorkspace()->getName(),
                ],
            ],
            null,
            null,
            null,
            $role,
            $role->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
    }
}
