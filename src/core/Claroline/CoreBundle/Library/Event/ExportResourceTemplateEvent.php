<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ExportResourceTemplateEvent extends Event
{
    private $resource;
    private $config;
    private $archive;
    private $resourceDependencies;

    public function __construct(AbstractResource $resource, \ZipArchive $archive)
    {
        $this->resource = $resource;
        $this->archive = $archive;
        $this->resourceDependencies = array();
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Sets the resource dependencies
     *
     * @param array
     */
    public function setResourceDependencies(array $resourceDependencies)
    {
        $this->resourceDependencies = $resourceDependencies;
    }

    /**
     * Gets the resource dependencies
     *
     * @return array
     */
    public function getResourceDependencies()
    {
        return $this->resourceDependencies;
    }
}
