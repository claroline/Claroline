<?php

namespace Claroline\CoreBundle\Event\Event\Log;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class LogResourceReadEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource_read';

    /**
     * Constructor.
     */
    public function __construct(AbstractResource $resource)
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

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->resource->getId();
    }
}