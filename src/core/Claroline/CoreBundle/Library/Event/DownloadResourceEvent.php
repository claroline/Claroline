<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a custom action is asked on a resource.
 */
class DownloadResourceEvent extends Event
{
    private $resource;
    private $item;

    /**
     * Constructor.
     *
     * @param integer $resourceId
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return integer
     */
    public function getResource()
    {
        return $this->resource;
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