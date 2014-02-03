<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;


class MaintenancePageUpdater
{
    private $baseFile;
    private $displayedFile;

    public function __construct($rootDir)
    {
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