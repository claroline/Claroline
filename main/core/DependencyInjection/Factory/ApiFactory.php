<?php

namespace Claroline\CoreBundle\DependencyInjection\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ApiFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.fos_oauth_server.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('fos_oauth_server.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
            ;

        $listenerId = 'security.authentication.listener.fos_oauth_server.'.$id;
        $container->setDefinition($listenerId, new DefinitionDecorator('claroline.core_bundle.library.security.authentication.claroline_api_listener'));

        return array($providerId, $listenerId, 'fos_oauth_server.security.entry_point');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'claro_api';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
