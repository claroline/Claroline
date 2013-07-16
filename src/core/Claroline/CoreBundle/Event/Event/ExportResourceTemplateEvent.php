<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ExportResourceTemplateEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $config;
    private $resourceDependencies;
    private $files;
    private $isPopulated = false;

    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
        $this->resourceDependencies = array();
        $this->files = array();
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setConfig(array $config)
    {
        $this->isPopulated = true;
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Expects an array of files.
     * Each item of the array is an array with the following keys:
     * 'archive_path' => '/pathname/in/archive'
     * 'original_path' => '/pathname/in/project'
     *
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
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

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
