<?php

namespace Claroline\AuthenticationBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater140000 extends Updater
{
    private ObjectManager $om;

    private PlatformConfigurationHandler $config;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config
    ) {
        $this->om = $om;
        $this->config = $config;
    }

    public function postUpdate(): void
    {
        $this->log('Update AuthenticationParameters ...');

        $authenticationParameters = $this->om->getRepository(AuthenticationParameters::class)->findOneBy([], ['id' => 'DESC']);
        if (empty($authenticationParameters)) {
            $authenticationParameters = new AuthenticationParameters();
        }

        $authenticationParameters->setHelpMessage($this->config->getParameter('authentication.help'));
        $authenticationParameters->setChangePassword($this->config->getParameter('authentication.changePassword'));
        $authenticationParameters->setInternalAccount($this->config->getParameter('authentication.internalAccount'));
        $authenticationParameters->setShowClientIp($this->config->getParameter('authentication.showClientIp'));
        $redirectOption = $this->config->getParameter('authentication.redirect_after_login_option');
        if (null !== $redirectOption) {
            $authenticationParameters->setRedirectAfterLoginOption(AuthenticationParameters::REDIRECT_OPTIONS[$redirectOption]);
        } else {
            $authenticationParameters->setRedirectAfterLoginOption(AuthenticationParameters::DEFAULT_REDIRECT_OPTION);
        }
        $authenticationParameters->setRedirectAfterLoginUrl($this->config->getParameter('authentication.redirect_after_login_url'));

        $this->om->persist($authenticationParameters);
        $this->om->flush();
    }
}
