<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\DependencyInjection\Compiler;

use Claroline\CoreBundle\DependencyInjection\Compiler\GeoipPass;
use Claroline\CoreBundle\Library\GeoIp\MaxMindGeoIpInfoProvider;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GeoipPassTest extends MockeryTestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->setParameter('claroline.param.geoip_db_path', __DIR__.'/dummy.mmdb');
        $container->register(MaxMindGeoIpInfoProvider::class);

        $pass = new GeoipPass();
        $pass->process($container);

        $this->assertFalse($container->hasDefinition(MaxMindGeoIpInfoProvider::class));
    }
}
