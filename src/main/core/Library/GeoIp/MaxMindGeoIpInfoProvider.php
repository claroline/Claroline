<?php

namespace Claroline\CoreBundle\Library\GeoIp;

use GeoIp2\Database\Reader as MaxMindReader;
use GeoIp2\Exception\GeoIp2Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MaxMindGeoIpInfoProvider implements GeoIpInfoProviderInterface
{
    private $geoIpDatabase;
    private $logger;

    public function __construct(MaxMindReader $geoIpDatabase, ?LoggerInterface $logger = null)
    {
        $this->geoIpDatabase = $geoIpDatabase;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getGeoIpInfo(string $ip): ?GeoIpinfo
    {
        try {
            return GeoIpInfo::fromMaxMind($this->geoIpDatabase->city($ip));
        } catch (GeoIp2Exception $e) {
            $this->logger->notice('Unable to get geoip info.', ['exception' => $e]);

            return null;
        }
    }
}
