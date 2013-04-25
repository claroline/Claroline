<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceShortcutEvent extends LogGenericEvent
{
    const ACTION = 'resource_shortcut';

    /**
     * Constructor.
     * $resource is the final shortcut object
     * while $source is the original object
     */
    public function __construct($resource, $source)
    {
        parent::__construct(
            self::ACTION,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay()
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'owner' => array(
                    'lastName' => $resource->getCreator()->getLastName(),
                    'firstName' => $resource->getCreator()->getFirstName()
                ),
                'source' => array(
                    'resource' => array(
                        'id' => $source->getId(),
                        'name' => $source->getName(),
                        'path' => $source->getPathForDisplay()
                    ),
                    'workspace' => array(
                        'id' => $source->getWorkspace()->getId(),
                        'name' => $source->getWorkspace()->getName()
                    ),
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