<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ImportResourceTemplateEvent extends Event
{
    private $parent;
    private $config;
    private $archive;

    public function __construct(array $config, AbstractResource $parent, \ZipArchive $archive)
    {
        $this->parent = $parent;
        $this->config = $config;
        $this->archive = $archive;
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
}