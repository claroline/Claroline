<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceShortcutEvent extends LogGenericEvent
{
    const ACTION = 'resource_shortcut';

    /**
     * Constructor.
     * $resource is the final shortcut object
     * while $source is the original object
     */
    public function __construct(ResourceNode $node, ResourceNode $source)
    {
        parent::__construct(
            self::ACTION,
            array(
                'resource' => array(
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay()
                ),
                'workspace' => array(
                    'name' => $node->getWorkspace()->getName()
                ),
                'owner' => array(
                    'lastName' => $node->getCreator()->getLastName(),
                    'firstName' => $node->getCreator()->getFirstName()
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
            $node,
            null,
            $node->getWorkspace(),
            $node->getCreator()
        );
    }
}
