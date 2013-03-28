<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;

class ImportResourceTemplateEvent extends Event
{
    private $parent;
    private $config;
    private $resource;
    private $createdResources;
    private $user;
    private $files;

    public function __construct(array $config, AbstractResource $parent, User $user)
    {
        $this->parent = $parent;
        $this->config = $config;
        $this->user = $user;
        $this->createdResources = array();
        $this->files = array();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getParent()
    {
        return $this->parent;
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

    public function getUser()
    {
        return $this->user;
    }

    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Expects an array of files.
     * Each item of the array is an array with the following keys:
     * 'archive_path' => '/pathname/in/archive'
     * 'original_path' => '/pathname/in/extracted/dir'
     *
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }
}