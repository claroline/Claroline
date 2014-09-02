<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater032000
{
    private $configHandler;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
    }

    public function postUpdate()
    {
        $this->usernameRegexUpdate();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }

    private function usernameRegexUpdate()
    {
        $this->log('Updating user name regex...');
        $this->configHandler->setParameter('username_regex', '/^[a-zA-Z0-9@_\.]*$/');
    }
}
