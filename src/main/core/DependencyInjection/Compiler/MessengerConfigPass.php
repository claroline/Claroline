<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var PlatformConfigurationHandler $platformConfig */
        $platformConfig = $container->get(PlatformConfigurationHandler::class);

        if ($platformConfig->getParameter('job_queue.enabled')) {
            switch ($platformConfig->getParameter('job_queue.transport')) {
                case 'doctrine':
                    $platformConfig->setParameter('job_queue.dsn', 'doctrine://default');
                    break;
                case 'redis':
                    $platformConfig->setParameter(
                        'job_queue.dsn',
                        'redis://password@localhost:6379?auto_setup=true&dbindex=1'
                    );
                    break;
            }
        } else {
            $platformConfig->setParameter('job_queue.dsn', 'sync://');
        }
    }
}
