<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceReadEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource-read';

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

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->resource->getId();
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
