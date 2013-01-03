<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ResourceCollection
{
    private $resources;
    private $errors;

    public function __construct($resources = array())
    {
        $this->resources = $resources;
        $this->errors = array();
    }

    public function addResource(AbstractResource $resource)
    {

        $this->resources[] = $resource;
    }

    public function setResources($resources)
    {
        $this->resources = $resources;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
