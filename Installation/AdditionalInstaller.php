<?php
namespace Icap\PortfolioBundle\Installation;

use Icap\PortfolioBundle\Installation\Updater\Updater000103;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '0.1.3', '<')) {
            $updater = new Updater000103($this->container->get('doctrine.orm.entity_manager'));
            $updater->postUpdate();
        }
    }
}