<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ImportResourceArrayEvent extends Event
{
    public function __construct(array $config, AbstractResource $parent)
    {
        $this->parent = $parent;
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getParent()
    {
        return $this->parent;
    }
}