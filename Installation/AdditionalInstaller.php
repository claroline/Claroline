<?php
namespace Icap\BlogBundle\Installation;

use Icap\BlogBundle\Installation\Updater\Updater;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        echo "<pre>";
        var_dump($currentVersion);
        echo "</pre>" . PHP_EOL;
        echo "<pre>";
        var_dump($targetVersion);
        echo "</pre>" . PHP_EOL;
        die("SSSSSTTTTTTOOOOOOPPPPPPP" . PHP_EOL);
        if (version_compare($currentVersion, '1.3', '<') && version_compare($currentVersion, '1.2', '>=') && version_compare($targetVersion, '1.3', '>=') ) {
            $updater = new Updater($this->container->get('doctrine.orm.entity_manager'));
            $updater->setLogger($this);
            $updater->postUpdate();
        }
    }
}