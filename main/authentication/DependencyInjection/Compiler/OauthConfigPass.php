<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\DependencyInjection\Compiler;

use Claroline\AuthenticationBundle\Model\Oauth\OauthConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OauthConfigPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * Rewrites previous service definitions in order to force the dumped container to use
     * dynamic configuration parameters. Technique may vary depending on the target service
     * (see for example https://github.com/opensky/OpenSkyRuntimeConfigBundle).
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $factory = new Reference('claroline.oauth.hwi.resource_owner_factory');
        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $resourceOwnerNoSpaces = str_replace(' ', '', $resourceOwner);
            $conf = new Definition();
            $conf->setClass($resourceOwnerNoSpaces.'ResourceOwner');
            $conf->setFactory([
                $factory,
                "get{$resourceOwnerNoSpaces}ResourceOwner",
            ]);
            $container->removeDefinition(
                'hwi_oauth.resource_owner.'.str_replace(' ', '_', strtolower($resourceOwner))
            );
            $container->setDefinition(
                'hwi_oauth.resource_owner.'.str_replace(' ', '_', strtolower($resourceOwner)),
                $conf
            );
        }
    }
}
