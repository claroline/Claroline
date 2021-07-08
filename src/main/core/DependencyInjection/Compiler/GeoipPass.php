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

use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\CoreBundle\Library\GeoIp\MaxMindGeoIpInfoProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Removes the optional geoip-related services if the geoip database is not available.
 */
class GeoipPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!(new Filesystem())->exists($container->getParameter('claroline.param.geoip_db_path'))) {
            $container->removeDefinition(MaxMindGeoIpInfoProvider::class);
            $container->removeAlias(GeoIpInfoProviderInterface::class);
        }
    }
}
