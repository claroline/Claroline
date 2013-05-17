<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceUpdateEvent extends LogGenericEvent
{
    const ACTION = 'resource_update';
    const ACTION_RENAME = 'resource_update_rename';

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
    public function __construct($resource, $changeSet)
    {
        $action = self::ACTION;
        if ($changeSet != null and count($changeSet) == 1 and array_key_exists('name', $changeSet)) {
            $action = self::ACTION_RENAME;
        }

        parent::__construct(
            $action,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay(),
                    'changeSet' => $changeSet
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'owner' => array(
                    'lastName' => $resource->getCreator()->getLastName(),
                    'firstName' => $resource->getCreator()->getFirstName()
                )
            ),
            null,
            null,
            $resource,
            null,
            $resource->getWorkspace(),
            $resource->getCreator()
        );
    }
}