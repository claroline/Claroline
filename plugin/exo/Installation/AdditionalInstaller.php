<?php

namespace UJM\ExoBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use UJM\ExoBundle\Installation\Updater\Updater060000;
use UJM\ExoBundle\Installation\Updater\Updater060001;
use UJM\ExoBundle\Installation\Updater\Updater060200;
use UJM\ExoBundle\Installation\Updater\Updater070000;
use UJM\ExoBundle\Installation\Updater\Updater090000;
use UJM\ExoBundle\Installation\Updater\Updater090002;
use UJM\ExoBundle\Installation\Updater\Updater090200;

class AdditionalInstaller extends BaseInstaller
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $updater = new Updater060000($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }

        if (version_compare($currentVersion, '6.0.0', '=')) {
            $updater = new Updater060001($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }

        if (version_compare($currentVersion, '6.2.0', '<')) {
            $updater = new Updater060200($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }

        if (version_compare($currentVersion, '7.0.0', '<=')) {
            $updater = new Updater070000($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $updater = new Updater060000($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '6.2.0', '<')) {
            $updater = new Updater060200($this->container->get('doctrine.dbal.default_connection'));
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '9.0.0', '<')) {
            $updater = new Updater090000(
                $this->container->get('doctrine.dbal.default_connection'),
                $this->container->get('claroline.persistence.object_manager'),
                $this->container->get('ujm_exo.serializer.exercise'),
                $this->container->get('ujm_exo.serializer.step'),
                $this->container->get('ujm_exo.serializer.item')
            );
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '9.0.2', '<')) {
            $updater = new Updater090002(
                $this->container->get('doctrine.dbal.default_connection')
            );
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '9.2.0', '<')) {
            $updater = new Updater090200(
                $this->container->get('doctrine.dbal.default_connection')
            );
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
