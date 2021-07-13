<?php

namespace Claroline\CoreBundle\Library\GeoIp;

use GeoIp2\Model\City;

/**
 * Holds Geolocation information for a given IP address.
 */
final class GeoIpInfo
{
    private $country;
    private $region;
    private $city;
    private $zipcode;
    private $timezone;
    private $latitude;
    private $longitude;

    public function __construct(
        ?string $country = null,
        ?string $region = null,
        ?string $city = null,
        ?string $zipcode = null,
        ?string $timezone = null,
        ?string $latitude = null,
        ?string $longitude = null
    ) {
        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        $this->zipcode = $zipcode;
        $this->timezone = $timezone;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromMaxMind(City $info): self
    {
        return new self(
            $info->country->names['en'] ?? null,
            $info->mostSpecificSubdivision->names['en'] ?? null,
            $info->city->name ?? null,
            $info->postal->code ?? null,
            $info->location->timeZone ?? null,
            $info->location->latitude ?? null,
            $info->location->longitude ?? null
        );
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }
}
