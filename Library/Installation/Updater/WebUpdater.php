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


class WebUpdater
{
    private $files;

    public function __construct($rootDir)
    {
        $baseMaintenance = "{$rootDir}/../vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/views/Maintenance/maintenance.html";
        $webMaintenance = "{$rootDir}/../web/maintenance.html";
        $baseApp = "{$rootDir}/../vendor/claroline/core-bundle/Claroline/CoreBundle/Resources/web/app.php";
        $webApp = "{$rootDir}/../web/app.php";

        $this->files = array(
            $baseMaintenance => $webMaintenance,
            $baseApp => $webApp
        );

    }

    public function preUpdate()
    {
        foreach ($this->files as $original => $web) {

            if (file_exists($web)) {
                unlink($web);
            }

            copy($original, $web);
        }
    }
} 