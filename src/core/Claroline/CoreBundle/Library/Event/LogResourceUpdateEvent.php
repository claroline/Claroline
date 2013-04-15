<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceUpdateEvent extends LogGenericEvent
{
    const action = 'resource_update';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * ('propertyName1' => ['property old value 1', 'property new value 1'], 'propertyName2' => ['property old value 2', 'property new value 2'] etc.)
     * 
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($resource, $changeSet)
    {
        parent::__construct(
            self::action,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay(),
                    'change_set' => $changeSet
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'owner' => array(
                    'last_name' => $resource->getCreator()->getLastName(),
                    'first_name' => $resource->getCreator()->getFirstName()
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