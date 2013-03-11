<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

class ExportWorkspaceEvent extends Event
{
    private $config;

    public function __construct(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
        $this->config = null;
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
}