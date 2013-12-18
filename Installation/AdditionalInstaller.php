<?php

namespace Claroline\ForumBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    private $logger;

    public function __construct()
    {
        $self = $this;
        $this->logger = function ($message) use ($self) {
            $self->log($message);
        };
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.2.0', '<') && version_compare($targetVersion, '2.1.2', '>=') ) {
            $updater020200 = new Updater\Updater020200($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.2.0', '<') && version_compare($targetVersion, '2.1.2', '>=') ) {
            $updater020200 = new Updater\Updater020200($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->postUpdate();
        }
    }
}