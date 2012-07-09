<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;

class DeleteResourceEvent extends Event
{
    private $resources;

    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    public function getResources()
    {
        return $this->resources;
    }
}