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

/**
 * Configures messenger to be async or sync depending on the "job_queue.enabled" platform option.
 */
class MessengerConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('messenger.transport.default')) {
            throw new \LogicException('Unable to configure messenger, make sure it is correctly configured.');
        }

        /** @var PlatformConfigurationHandler $platformConfig */
        $platformConfig = $container->get(PlatformConfigurationHandler::class);
        $transportDefinition = $container->getDefinition('messenger.transport.default');

        if (!$platformConfig->getParameter('job_queue.enabled')) {
            $transportDefinition->replaceArgument(0, 'sync://');
        }
    }
}
