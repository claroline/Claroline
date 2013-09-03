<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceCopyEvent extends LogGenericEvent
{
    const ACTION = 'resource_copy';

    /**
     * Constructor.
     * $resource is the final copy
     * while $source is the original object
     */
    public function __construct(ResourceNode $resource, ResourceNode $source)
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
                    )
                )
            ),
            null,
            null,
            $resource,
            null,
            $resource->getWorkspace(),
            $resource->getCreator()
        );

        $this->isDisplayedInWorkspace(true);
    }
}
