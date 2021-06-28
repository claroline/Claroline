<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130023 extends Updater
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        PlatformConfigurationHandler $config
    ) {
        $this->config = $config;
    }

    public function postUpdate()
    {
        if ($this->config->getParameter('registration.force_organization_creation')) {
            $this->config->setParameter('registration.organization_selection', 'create');
        }
    }
}
