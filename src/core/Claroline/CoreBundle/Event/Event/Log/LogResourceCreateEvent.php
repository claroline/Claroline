<?php

namespace Claroline\CoreBundle\Event\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceCreateEvent extends LogGenericEvent
{
    const ACTION = 'resource_create';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $resource)
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
