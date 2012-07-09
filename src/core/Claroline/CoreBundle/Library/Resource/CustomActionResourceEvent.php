<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class CustomActionResourceEvent extends Event
{
    private $resourceId;
    private $response;

    public function __construct($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}