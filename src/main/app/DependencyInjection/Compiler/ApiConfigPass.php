<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\DependencyInjection\Compiler;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\AppBundle\API\ValidatorProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->register($container, FinderProvider::class, 'claroline.finder');
        $this->register($container, ValidatorProvider::class, 'claroline.validator');
        $this->register($container, TransferProvider::class, 'claroline.transfer.adapter');
        $this->register($container, TransferProvider::class, 'claroline.transfer.action');
        $this->register($container, SerializerProvider::class, 'claroline.serializer');
    }

    private function register(ContainerBuilder $container, $provider, $registerTag)
    {
        if (false === $container->hasDefinition($provider)) {
            return;
        }

        $providerDef = $container->getDefinition($provider);

        $taggedServices = $container->findTaggedServiceIds($registerTag);

        foreach (array_keys($taggedServices) as $id) {
            $providerDef->addMethodCall('add', [new Reference($id)]);
        }
    }
}
