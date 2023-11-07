<?php

namespace Claroline\AuthenticationBundle\Installation\Updater;

use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater140000 extends Updater
{
    private AuthenticationManager $authenticationManager;

    private PlatformConfigurationHandler $config;

    public function __construct(
        AuthenticationManager $authenticationManager,
        PlatformConfigurationHandler $config
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->config = $config;
    }

    public function postUpdate(): void
    {
        $this->log('Update AuthenticationParameters ...');

        $authenticationParameters = $this->authenticationManager->getParameters();

        $authenticationParameters->setHelpMessage($this->config->getParameter('authentication.help'));
        $authenticationParameters->setChangePassword($this->config->getParameter('authentication.changePassword'));
        $authenticationParameters->setInternalAccount($this->config->getParameter('authentication.internalAccount'));
        $authenticationParameters->setShowClientIp($this->config->getParameter('authentication.showClientIp'));

        $this->authenticationManager->updateParameters($authenticationParameters);
    }
}
