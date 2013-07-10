<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class PlayFileEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $response;
    private $isPopulated = false;

    /**
     * Constructor.
     *
     * @param integer $resourceId
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource on which the action is to be taken.
     *
     * @return Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the response for the action.
     *
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->isPopulated = true;
        $this->response = $response;
    }

    /**
     * Returns the response for the action.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}