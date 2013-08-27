<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'resource_delete';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $node)
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
