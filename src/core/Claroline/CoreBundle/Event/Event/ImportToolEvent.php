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
    private $roles;

    /**
     *
     * @param AbstractWorkspace $workspace The workspace
     * @param array             $config    The config array from the template
     * @param Directory         $root      The workspace root
     * @param User              $user      The creator
     * @param array             $filePaths The requireded files from the template
     * @param array             $roles     The role list wich is needed for the creation;
     */
    public function __construct(
        AbstractWorkspace $workspace,
        array $config,
        Directory $root,
        User $user,
        array $filePaths,
        array $roles
    )
    {
        $this->workspace = $workspace;
        $this->config = $config;
        $this->root = $root;
        $this->user = $user;
        $this->filePaths = $filePaths;
        $this->roles = $roles;
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

    /**
     * extracted files from the archive.
     */
    public function getFilePaths()
    {
        return $this->filePaths;
    }

    /**
     * extracted files from the archive.
     */
    public function setFilePaths($filePaths)
    {
        $this->filePaths = $filePaths;
    }

    public function getRoles()
    {
        return $this->roles;
    }
}
