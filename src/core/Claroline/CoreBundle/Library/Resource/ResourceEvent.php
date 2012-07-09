<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;

class ResourceEvent extends Event
{
    private $response;

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }
}