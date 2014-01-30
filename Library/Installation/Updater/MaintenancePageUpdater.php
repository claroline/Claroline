<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 30/01/14
 * Time: 16:54
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;


class MaintenancePageUpdater {

    private $baseFile;
    private $displayedFile;

    public function __construct($rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->baseFile = "{$rootDir}/../vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/views/Maintenance/maintenance.html";
        $this->displayedFile = "{$rootDir}/../web/maintenance.html";
    }

    public function preUpdate()
    {
        if (file_exists($this->displayedFile)) {
            unlink($this->displayedFile);
        }

        copy($this->baseFile, $this->displayedFile);
    }
} 