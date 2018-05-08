<?php

namespace Claroline\AppBundle\Manager\File;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @DI\Service("claroline.manager.temp_file")
 */
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

    /**
     * TempManager constructor.
     *
     * @DI\InjectParams({
     *      "config" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param PlatformConfigurationHandler $config
     */
    public function __construct(
        PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    /**
     * Get the path to the temp directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->config->getParameter('tmp_dir');
    }

    /**
     * Generates a new temporary file.
     *
     * This may also handle the real creation of the file and returns the File object.
     * But for now it's only used by archive generation (which uses \ZipArchive for this and only needs the path).
     *
     * @return string - the path to the temp file
     */
    public function generate()
    {
        // generates an unique name for the new temp
        $tempName = Uuid::uuid4()->toString();
        $tempFile = $this->getDirectory().DIRECTORY_SEPARATOR.$tempName;

        $this->files[] = $tempFile;

        return $tempFile;
    }

    /**
     * Removes current temp files.
     */
    public function clear()
    {
        if (!empty($this->files)) {
            $fs = new FileSystem();
            foreach ($this->files as $file) {
                $fs->remove($file);
            }
        }
    }
}
