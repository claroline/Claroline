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

class MailingConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $transport = new Definition();
        $transport->setClass(TransportInterface::class);
        $transport->setFactory([
            new Reference('Claroline\CoreBundle\Library\Mailing\TransportFactory'),
            'getTransport',
        ]);
        $container->removeDefinition('mailer.default_transport');
        $container->setDefinition('mailer.default_transport', $transport);
        $container->removeDefinition('mailer.transports');
        $container->register('mailer.transports', Transports::class)->addArgument([new Reference('mailer.default_transport')]);

        if (false === $container->hasDefinition('Claroline\CoreBundle\Library\Mailing\Mailer')) {
            return;
        }

        $providerDef = $container->getDefinition('Claroline\CoreBundle\Library\Mailing\Mailer');

        $taggedServices = $container->findTaggedServiceIds('claroline.mailing');

        foreach (array_keys($taggedServices) as $id) {
            $providerDef->addMethodCall('add', [new Reference($id)]);
        }
    }
}
