<?php
namespace Icap\BlogBundle\Installation;

use Icap\BlogBundle\Installation\Updater\Updater;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if ("9999999-dev" === $currentVersion) {
            $updater = new Updater($this->container->get('doctrine.orm.entity_manager'));
            $updater->postUpdate();
        }
    }
}