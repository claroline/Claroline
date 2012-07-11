<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event dispatched by the resource controller when a custom action is asked on a resource.
 */
class CustomActionResourceEvent extends Event
{
    private $resourceId;
    private $response;

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