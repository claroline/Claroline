<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource copy is asked.
 */
class CopyResourceEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $copy;
    private $isPopulated = false;

    /**
     * Constructor.
     *
     * @param AbstractResource $resource
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource to be copied.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the copy of the original resource.
     *
     * @param AbstractResource $copy
     */
    public function setCopy(AbstractResource $copy)
    {
        $this->isPopulated = true;
        $this->copy = $copy;
    }

    /**
     * Returns the copy of the original resource.
     *
     * @return AbstractResource
     */
    public function getCopy()
    {
        return $this->copy;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
