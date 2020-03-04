<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120534 extends Updater
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->config = $container->get(PlatformConfigurationHandler::class);
    }

    public function postUpdate()
    {
        $this->log('Add widget "messages" in header');

        $header = $this->config->getParameter('header');
        if (empty($header)) {
            $header = [];
        }

        // push message
        $header[] = 'messages';
        // save param
        $this->config->setParameter('header', $header);
    }
}
