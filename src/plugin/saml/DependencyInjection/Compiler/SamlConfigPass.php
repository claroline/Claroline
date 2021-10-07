<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle\DependencyInjection\Compiler;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SamlConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var PlatformConfigurationHandler $configHandler */
        $configHandler = $container->get(PlatformConfigurationHandler::class);

        // this will allow to send the user cookie to the IDP to make redirection work
        $container->setParameter('claroline.session_cookie_samesite', 'none');

        $container->setParameter('entity_id', $configHandler->getParameter('saml.entity_id'));
        $container->setParameter('lightsaml.own.entity_id', $configHandler->getParameter('saml.entity_id'));
        $container->setParameter('credentials', $configHandler->getParameter('saml.credentials'));

        $idp = $configHandler->getParameter('saml.idp');
        if (!empty($idp)) {
            $files = [];
            foreach ($idp as $provider) {
                $files[] = $provider['metadata'];
            }
            $container->setParameter('idp', $files);
        }

        // I need to reconfigure LightSaml to inject config form platform_options.json
        // There should be a better approach as I c/c code from base bundle and config in .yml is partially incorrect
        // maybe I should replace stores and make them handles it
        $this->configureOwnCredentials($container);

        $this->configureParty($container);
        $this->configureCredentialStore($container);
        $this->configureServiceCredentialResolver($container);
    }

    private function configureServiceCredentialResolver(ContainerBuilder $container)
    {
        if ($container->hasDefinition('lightsaml.service.credential_resolver')) {
            $definition = $container->getDefinition('lightsaml.service.credential_resolver');
            $definition->setFactory([new Reference('lightsaml.service.credential_resolver_factory'), 'build']);
        }
    }

    private function configureCredentialStore(ContainerBuilder $container)
    {
        if ($container->hasDefinition('lightsaml.credential.credential_store')) {
            $definition = $container->getDefinition('lightsaml.credential.credential_store');
            $definition->setFactory([new Reference('lightsaml.credential.credential_store_factory'), 'buildFromOwnCredentialStore']);
        }
    }

    /**
     * Appends SP credentials declared in platform_options.json.
     */
    private function configureOwnCredentials(ContainerBuilder $container)
    {
        /** @var PlatformConfigurationHandler $configHandler */
        $configHandler = $container->get(PlatformConfigurationHandler::class);

        // adds credentials from platform_options.json
        $entityId = $configHandler->getParameter('saml.entity_id');
        $credentials = $configHandler->getParameter('saml.credentials');
        if (!empty($credentials)) {
            foreach ($credentials as $id => $data) {
                $definition = new Definition(
                    'LightSaml\Store\Credential\X509FileCredentialStore',
                    [
                        $entityId,
                        $data['certificate'],
                        $data['key'],
                        $data['password'],
                    ]
                );
                $definition->addTag('lightsaml.own_credential_store');
                $container->setDefinition('lightsaml.own.credential_store.'.$entityId.'.'.$id, $definition);
            }
        }
    }

    /**
     * Appends IDP metadata files declared in platform_options.json.
     */
    private function configureParty(ContainerBuilder $container)
    {
        if ($container->hasDefinition('lightsaml.party.idp_entity_descriptor_store')) {
            /** @var PlatformConfigurationHandler $configHandler */
            $configHandler = $container->get(PlatformConfigurationHandler::class);

            $idp = $configHandler->getParameter('saml.idp');
            if (!empty($idp)) {
                $idpFiles = array_map(function (array $provider) {
                    return $provider['metadata'];
                }, $idp);
                $store = $container->getDefinition('lightsaml.party.idp_entity_descriptor_store');
                foreach ($idpFiles as $id => $file) {
                    $id = sprintf('lightsaml.party.idp_entity_descriptor_store.file.%s', $id);

                    $container
                        ->setDefinition($id, new ChildDefinition('lightsaml.party.idp_entity_descriptor_store.file'))
                        ->replaceArgument(0, $file);

                    $store->addMethodCall('add', [new Reference($id)]);
                }
            }
        }
    }
}
