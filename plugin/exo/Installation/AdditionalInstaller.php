<?php

namespace UJM\ExoBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use UJM\ExoBundle\Installation\Updater\Updater060000;
use UJM\ExoBundle\Installation\Updater\Updater060001;
use UJM\ExoBundle\Installation\Updater\Updater060200;
use UJM\ExoBundle\Installation\Updater\Updater070000;
use UJM\ExoBundle\Installation\Updater\Updater090000;
use UJM\ExoBundle\Installation\Updater\Updater090002;
use UJM\ExoBundle\Installation\Updater\Updater090200;
use UJM\ExoBundle\Installation\Updater\Updater100200;
use UJM\ExoBundle\Installation\Updater\Updater100600;
use UJM\ExoBundle\Installation\Updater\Updater120000;
use UJM\ExoBundle\Installation\Updater\Updater120308;
use UJM\ExoBundle\Installation\Updater\Updater120400;
use UJM\ExoBundle\Installation\Updater\Updater120403;
use UJM\ExoBundle\Installation\Updater\Updater120406;
use UJM\ExoBundle\Installation\Updater\Updater120410;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
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

        if (version_compare($currentVersion, '10.6.0', '<')) {
            $updater = new Updater100600($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '11.0.0', '<')) {
            $updater = new Updater100200($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.0.0', '<')) {
            $updater = new Updater120000($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.0', '<')) {
            $updater = new Updater120400($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.1', '<')) {
            $updater = new Updater120308($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.3', '<')) {
            $updater = new Updater120403($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.6', '<')) {
            $updater = new Updater120406($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.15', '<')) {
            $updater = new Updater120410($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
