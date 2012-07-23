<?php

namespace Claroline\CoreBundle\Library\Resource\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event dispatched by the resource controller when a custom action is asked on a resource.
 */
class ExportResourceEvent extends Event
{
    private $resourceId;
    private $item;

    /**
     * Constructor.
     *
     * @param integer $resourceId
     */
    public function __construct($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Sets the exported item.
     *
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * Returns the response for the action.
     *
     * @return Response
     */
    public function getItem()
    {
        return $this->item;
    }
}