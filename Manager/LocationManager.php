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

/**
 * @DI\Service("claroline.manager.location_manager")
 */
class LocationManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *       "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Organization\Location');
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
        //do stuff here
    }
} 