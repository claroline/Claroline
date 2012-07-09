<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class CopyResourceEvent extends Event
{
    private $resource;
    private $copy;

    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getCopy()
    {
        return $this->copy;
    }

    public function setCopy(AbstractResource $copy)
    {
        $this->copy = $copy;
    }
}