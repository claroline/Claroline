<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class CreateResourceEvent extends Event
{
    private $resource;
    private $formContent;

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setErrorFormContent($formContent)
    {
        $this->formContent = $formContent;
    }

    public function getErrorFormContent()
    {
        return $this->formContent;
    }
}