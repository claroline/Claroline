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
use Symfony\Component\EventDispatcher\Event;

class ExportToolEvent extends Event
{
    private $config;
    private $workspace;
    private $files;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
        $this->config = null;
        $this->files = array();
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

    /**
     * Expects an array of files.
     * Each item of the array is an array with the following keys:
     * 'archive_path' => '/pathname/in/archive'
     * 'original_path' => '/pathname/in/project'.
     *
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFilenamesFromArchive()
    {
        $files = array();

        foreach ($this->files as $file) {
            $files[] = $file['archive_path'];
        }

        return $files;
    }
}
