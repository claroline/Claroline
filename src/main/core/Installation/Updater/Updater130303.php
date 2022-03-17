<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130303 extends Updater
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
        // disable messenger by default as it require additional supervisor to run.
        $this->config->setParameter('job_queue.enabled', false);
    }
}
