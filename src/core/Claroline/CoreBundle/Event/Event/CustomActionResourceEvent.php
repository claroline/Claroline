<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;

/**
 * Event dispatched by the resource controller when a custom action is asked on a resource.
 */
class CustomActionResourceEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $response;
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
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return integer
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
        $this->isPopulated = true;
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
