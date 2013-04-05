<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceUpdateEvent extends LogGenericEvent
{
    const action = 'resource_update';

    /**
     * Constructor.
     * LogResourceUpdateEvent is used by CoreBundle or plugins when a resource's properties changed (e.g. name, icon, posts per page, comments per article, blog banner etc.)
     *
     * OldValues and newValues expected variables are arrays which contain all modified properties, in the following form:
     * ('property_name_1' => 'property_value_1', 'property_name_2' => 'property_value_2' etc.)
     * 
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct($resource, $oldValues, newValues)
    {
        parent::__construct(
            self::action,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay(),
                    'old_values' => $oldValues,
                    'new_values' => $newValues
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