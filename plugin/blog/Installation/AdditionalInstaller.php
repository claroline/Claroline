<?php

namespace Icap\BlogBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Icap\BlogBundle\Installation\Updater\UpdaterMaster;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if ('9999999-dev' === $currentVersion) {
            $updater = new UpdaterMaster($this->container->get('doctrine.orm.entity_manager'));
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
