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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\Transports;

class DynamicConfigPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * Rewrites previous service definitions in order to force the dumped container to use
     * dynamic configuration parameters. Technique may vary depending on the target service
     * (see for example https://github.com/opensky/OpenSkyRuntimeConfigBundle).
     */
    public function process(ContainerBuilder $container)
    {
        //mailing
        $transport = new Definition();
        $transport->setClass(TransportInterface::class);
        $transport->setFactory([
            new Reference('Claroline\CoreBundle\Library\Mailing\TransportFactory'),
            'getTransport', ]
        );
        $container->removeDefinition('mailer.default_transport');
        $container->setDefinition('mailer.default_transport', $transport);
        $container->removeDefinition('mailer.transports');
        $container->register('mailer.transports', Transports::class)->addArgument([new Reference('mailer.default_transport')]);

        //notification
        $container->setAlias('icap.notification.orm.entity_manager', 'Claroline\AppBundle\Persistence\ObjectManager');

        $definition = $container->findDefinition('security.authentication.listener.anonymous');
        $definition->setClass('Claroline\CoreBundle\Listener\AnonymousAuthenticationListener');
    }
}
