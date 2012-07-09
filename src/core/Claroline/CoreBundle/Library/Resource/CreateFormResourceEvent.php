<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;

class CreateFormResourceEvent extends Event
{
    private $responseContent;

    public function getResponseContent()
    {
        return $this->responseContent;
    }

    public function setResponseContent($responseContent)
    {
        $this->responseContent = $responseContent;
    }
}