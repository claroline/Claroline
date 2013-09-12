<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Event\MandatoryEventInterface;

class LogWorkspaceRoleChangeRightEvent extends LogGenericEvent implements MandatoryEventInterface
{
    const ACTION = 'workspace-role-change_right';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * )
     *
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($role, $resource, $changeSet)
    {
        parent::__construct(
            self::ACTION,
            array(
                'role' => array(
                    'name' => $role->getTranslationKey(),
                    'changeSet' => $changeSet
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay()
                )
            ),
            null,
            null,
            $resource,
            $role,
            $resource->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
