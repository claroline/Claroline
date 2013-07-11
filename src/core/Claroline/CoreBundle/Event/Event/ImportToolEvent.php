<?php

namespace Claroline\CoreBundle\Event\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class ImportToolEvent extends Event
{
    private $workspace;
    private $config;
    private $root;
    private $files;
    private $filePaths;

    public function __construct(
        AbstractWorkspace $workspace,
        $config,
        Directory $root,
        User $user,
        $filePaths
    )
    {
        $this->workspace = $workspace;
        $this->config = $config;
        $this->root = $root;
        $this->user = $user;
        $this->filePaths = $filePaths;
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

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFilePaths()
    {
        return $this->filePaths;
    }

    public function setFilePaths($filePaths)
    {
        $this->filePaths = $filePaths;
    }
}

