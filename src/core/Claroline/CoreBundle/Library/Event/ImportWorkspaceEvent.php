<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class ImportWorkspaceEvent extends Event
{
    private $workspace;
    private $config;
    private $archive;
    private $root;

    public function __construct(
        AbstractWorkspace $workspace,
        $config,
        \ZipArchive $archive,
        Directory $root,
        User $user
    )
    {
        $this->workspace = $workspace;
        $this->config = $config;
        $this->archive = $archive;
        $this->root = $root;
        $this->user = $user;
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

    public function getRoot()
    {
        return $this->root;
    }

    public function getUser()
    {
        return $this->user;
    }
}

