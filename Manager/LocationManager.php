<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @DI\Service("claroline.manager.location_manager")
 */
class LocationManager
{
    private $om;
    private $repo;
    private $ch;
    private $cm;

    /**
     * @DI\InjectParams({
     *       "om" = @DI\Inject("claroline.persistence.object_manager"),
     *       "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *       "cm" = @DI\Inject("claroline.manager.curl_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $ch,
        CurlManager $cm
    )
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Organization\Location');
        $this->ch = $ch;
        $this->cm = $cm;
    }

    public function create(Location $location)
    {
        $this->setCoordinates($location);
        $this->om->persist($location);
        $this->om->flush();

        return $location;
    }

    public function edit(Location $location)
    {
        $this->setCoordinates($location);
        $this->om->persist($location);
        $this->om->flush();

        return $location;
    }

    public function delete(Location $location)
    {
        $this->om->remove($location);
        $this->om->flush();
    }

    public function getByType($type)
    {
        return $this->repo->findBy(array('type' => $type));
    }

    public function setCoordinates(Location $location)
    {
        $data = $this->geolocate($location);
        $data = json_decode($data, true);
        $loc = $data['results'][0]['geometry']['location'];

        $location->setLongitude($loc['lng']);
        $location->setLatitude($loc['lat']);

        return $location;
    }

    public function geolocate(Location $location)
    {
        //this will only work for western europe because the format may be different for other countries... big switch
        //may be needed...
        $address = $location->getStreetNumber() . '+' . $location->getStreet() . '+' . $location->getPc() . '+'. $location->getTown() . '+' . $location->getCountry();
        $address = urlencode($address);
        $query = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $this->ch->getParameter('google_geocoding_key');

        return $this->cm->exec($query);
    }
} 