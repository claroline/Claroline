<?php

namespace Claroline\AppBundle\Manager\File;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TempFileManager
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /**
     * The list of current temp files.
     *
     * @var array
     */
    private $files = [];

    public function __construct(
        PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    /**
     * Get the path to the temp directory.
     */
    public function getDirectory(): string
    {
        return $this->config->getParameter('tmp_dir');
    }

    /**
     * Generates a new temporary file.
     *
     * This may also handle the real creation of the file and returns the File object.
     * But for now it's only used by archive generation (which uses \ZipArchive for this and only needs the path).
     *
     * NB. persisted files are not automatically cleared at the end of each requests (this is useful when the file is processed
     * in the messenger). YOU NEED TO MANUALLY REMOVE THE FILE WHEN YOU ARE DONE WITH IT.
     *
     * @return string - the path to the temp file
     */
    public function generate(?bool $persist = false): string
    {
        // generates an unique name for the new temp
        $tempName = Uuid::uuid4()->toString();
        $tempFile = $this->getDirectory().DIRECTORY_SEPARATOR.$tempName;

        if (!$persist) {
            $this->files[] = $tempFile;
        }

        return $tempFile;
    }

    /**
     * Copy a file inside the temp dir.
     *
     * NB. persisted files are not automatically cleared at the end of each requests (this is useful when the file is processed
     * in the messenger). YOU NEED TO MANUALLY REMOVE THE FILE WHEN YOU ARE DONE WITH IT.
     */
    public function copy(File $file, ?bool $persist = false)
    {
        // generates an unique name for the new temp
        $tempName = Uuid::uuid4()->toString();
        $tempFile = $this->getDirectory().DIRECTORY_SEPARATOR.$tempName;

        $file->move($this->getDirectory(), $tempName);
        if (!$persist) {
            $this->files[] = $tempFile;
        }

        return $tempFile;
    }

    /**
     * Removes current temp files.
     */
    public function clear(): void
    {
        if (!empty($this->files)) {
            $fs = new FileSystem();
            foreach ($this->files as $file) {
                $fs->remove($file);
            }
        }
    }
}
