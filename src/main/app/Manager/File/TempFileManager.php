<?php

namespace Claroline\AppBundle\Manager\File;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

class TempFileManager
{
    private Filesystem $filesystem;
    private string $tmpDir;

    /**
     * The list of current temp files.
     * This is used to purge the temp files created during the process of the current request.
     */
    private array $files = [];

    public function __construct(
        string $tmpDir
    ) {
        $this->tmpDir = $tmpDir;
        $this->filesystem = new FileSystem();
    }

    /**
     * Get the path to the temp directory.
     */
    public function getDirectory(): string
    {
        $fs = new Filesystem();
        if (!$fs->exists($this->tmpDir)) {
            $fs->mkdir($this->tmpDir);
        }

        return $this->tmpDir;
    }

    /**
     * Generates a new temporary file.
     *
     * This may also handle the real creation of the file and returns the File object.
     * But for now it's only used by archive generation (which uses \ZipArchive for this and only needs the path).
     *
     * NB. persisted files are not automatically cleared at the end of each request (this is useful when the file is processed
     * in the messenger). YOU NEED TO MANUALLY REMOVE THE FILE WHEN YOU ARE DONE WITH IT.
     *
     * @return string - the path to the temp file
     */
    public function generate(?bool $persist = false): string
    {
        // generates unique name for the new temp
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
     * NB. persisted files are not automatically cleared at the end of each request (this is useful when the file is processed
     * in the messenger). YOU NEED TO MANUALLY REMOVE THE FILE WHEN YOU ARE DONE WITH IT.
     */
    public function copy(\SplFileInfo $file, ?bool $persist = false): string
    {
        // generates unique name for the new temp
        $tempName = Uuid::uuid4()->toString();
        $tempFile = $this->getDirectory().DIRECTORY_SEPARATOR.$tempName;

        $this->filesystem->copy($file->getPathname(), $tempFile);
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
            foreach ($this->files as $file) {
                $this->filesystem->remove($file);
            }
        }
    }
}
