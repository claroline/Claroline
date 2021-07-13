<?php

namespace Claroline\CoreBundle\Library\GeoIp;

interface GeoIpInfoProviderInterface
{
    public function getGeoIpInfo(string $ip): ?GeoIpInfo;
}
