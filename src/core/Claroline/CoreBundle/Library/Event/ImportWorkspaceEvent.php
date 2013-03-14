<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

class ImportWorkspaceEvent extends Event
{
    private $workspace;
    private $config;
    private $archive;
    
    public function __construct(AbstractWorkspace $workspace, $config, \ZipArchive $archive)
    {
        $this->workspace = $workspace;
        $this->config = $config;
        $this->archive = $archive;
    }
    
    public function getArchive()
    {
        return $this->archive;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getConfig()
    {
        return $this->config;
    }
}

