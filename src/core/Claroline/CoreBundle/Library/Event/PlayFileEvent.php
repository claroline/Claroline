<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class PlayFileEvent extends Event
{
    private $resource;
    private $response;

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
}