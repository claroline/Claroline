<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

class ImportWorkspaceEvent extends Event
{
    public function __construct(AbstractWorkspace $workspace, $config)
    {
        $this->workspace = $workspace;
        $this->config = $config;
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

