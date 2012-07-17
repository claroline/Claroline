<?php

namespace Claroline\CoreBundle\Library\Resource\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource deletion is asked.
 */
class DeleteResourceEvent extends Event
{
    private $resources;

    /**
     * Constructor.
     *
     * @param array[AbstractResource] $resources
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * Returns the resource to be deleted.
     *
     * @return array[AbstractResource]
     */
    public function getResources()
    {
        return $this->resources;
    }
}