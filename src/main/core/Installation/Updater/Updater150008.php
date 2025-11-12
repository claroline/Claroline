<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater150008 extends Updater
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public function postUpdate(): void
    {
        $locale = $this->config->getParameter('locales.default');
        if ($locale) {
            $this->config->setParameter('intl.locale', $locale);
        }
    }
}
