<?php

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when the publication status of a
 * resource node is modified.
 */
class PublicationChangeEvent extends Event
{
    private $resource;

    /**
     * @param AbstractResource $resource
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
