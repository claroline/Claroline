<?php

namespace Claroline\PrivacyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\PrivacyBundle\Manager\PrivacyManager;

class Updater142000 extends Updater
{
    private PlatformConfigurationHandler $config;
    private PrivacyManager $privacyManager;
    private ObjectManager $om;

    public function __construct(
        PlatformConfigurationHandler $config,
        PrivacyManager $privacyManager,
        ObjectManager $om
    ) {
        $this->config = $config;
        $this->privacyManager = $privacyManager;
        $this->om = $om;
    }

    public function postUpdate(): void
    {
        $privacyParameters = $this->privacyManager->getParameters();
        $template = $privacyParameters->getTosTemplate();

        if (null === $template || $template->isSystem()) {
            $privacyParameters->setTosEnabled(false);
        }

        $this->privacyManager->updateParameters($privacyParameters);

        $this->config->setParameter('tos.enabled', false);

        $this->om->flush();
    }
}
