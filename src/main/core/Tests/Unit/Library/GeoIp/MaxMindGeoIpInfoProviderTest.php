<?php

namespace Claroline\CoreBundle\Tests\Unit\Library\GeoIp;

use Claroline\CoreBundle\Library\GeoIp\MaxMindGeoIpInfoProvider;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use GeoIp2\Database\Reader;
use GeoIp2\Model\City;
use Psr\Log\LoggerInterface;

class MaxMindGeoIpInfoProviderTest extends MockeryTestCase
{
    public function testSend()
    {
        $reader = $this->mock(Reader::class);
        $reader->shouldReceive('city')
            ->withArgs(['127.0.0.1'])
            ->andReturn(new City(['city' => ['names' => ['en' => 'Lyon']]]));

        $provider = new MaxMindGeoIpInfoProvider($reader, $this->mock(LoggerInterface::class));
        $this->assertSame('Lyon', $provider->getGeoIpInfo('127.0.0.1')->getCity());
    }
}
