<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ImportResourceTemplateEvent extends Event
{
    private $parent;
    private $config;
    private $archive;
    private $resource;
    private $createdResources;

    public function __construct(array $config, AbstractResource $parent, \ZipArchive $archive)
    {
        $this->parent = $parent;
        $this->config = $config;
        $this->archive = $archive;
        $this->createdResources = array();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getArchive()
    {
        return $this->archive;
    }

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;

    }

    public function getResource()
    {
        return $this->resource;
    }

    public function find($key)
    {
        return $this->createdResources[$key];
    }

    public function addCreatedResource(AbstractResource $resource, $key)
    {
        $this->createdResources[$key] = $resource;
    }

    public function setCreatedResources(array $resources)
    {
        $this->createdResources = $resources;
    }

    public function mergeCreatedResources(array $resources)
    {
        $this->createdResources = array_merge($resources, $this->createdResources);
    }

    public function getCreatedResources()
    {
        return $this->createdResources;
    }
}