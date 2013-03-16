<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

class ExportWorkspaceEvent extends Event
{
    private $config;
    private $workspace;
    private $archive;

    public function __construct(AbstractWorkspace $workspace, \ZipArchive $archive)
    {
        $this->workspace = $workspace;
        $this->config = null;
        $this->archive = $archive;
    }

    public function getWorkspace()
    {
        return $this->workspace;
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