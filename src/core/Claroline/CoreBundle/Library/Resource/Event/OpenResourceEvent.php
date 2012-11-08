<?php

namespace Claroline\CoreBundle\Library\Resource\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource is open
 */
class OpenResourceEvent extends Event
{
    private $instanceId;
    private $response;

    public function __construct($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}


