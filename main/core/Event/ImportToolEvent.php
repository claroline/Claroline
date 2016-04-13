<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
     * @param Workspace    $workspace The workspace
     * @param array        $config    The config array from the template
     * @param ResourceNode $root      The workspace root
     * @param User         $user      The creator
     * @param array        $filePaths The requireded files from the template
     * @param array        $roles     The role list wich is needed for the creation;
     */
    public function __construct(
        Workspace $workspace,
        array $config,
        ResourceNode $root,
        User $user,
        array $filePaths,
        array $roles
    ) {
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
