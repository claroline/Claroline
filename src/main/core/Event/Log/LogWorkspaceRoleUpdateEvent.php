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

class LogWorkspaceRoleUpdateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-role-update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * ).
     *
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($role, $changeSet)
    {
        parent::__construct(
            self::ACTION,
            array(
                'role' => array(
                    'name' => $role->getName(),
                    'changeSet' => $changeSet,
                ),
                'workspace' => array(
                    'name' => $role->getWorkspace()->getName(),
                ),
            ),
            null,
            null,
            null,
            $role,
            $role->getWorkspace()
        );

        $this->setIsDisplayedInWorkspace(true);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
    }
}
