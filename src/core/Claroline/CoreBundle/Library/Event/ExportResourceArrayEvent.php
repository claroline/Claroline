<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ExportResourceArrayEvent extends Event
{
    private $resource;
    private $config;
    private $archive;

    public function __construct(AbstractResource $resource, \ZipArchive $archive)
    {
        $this->resource = $resource;
        $this->archive = $archive;
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
}
