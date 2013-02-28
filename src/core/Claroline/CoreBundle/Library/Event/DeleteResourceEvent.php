<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource deletion is asked.
 */
class DeleteResourceEvent extends Event
{
    private $resource;

    /**
     * Constructor.
     *
     * @param AbstractResource $resources
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource to be deleted.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}