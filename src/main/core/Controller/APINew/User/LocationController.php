<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/location")
 */
class LocationController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;
    use HasOrganizationsTrait;

    /** @var LocationManager */
    private $manager;

    public function __construct(LocationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'location';
    }

    public function getClass()
    {
        return Location::class;
    }

    /**
     * @Route("/{id}/geolocate", name="apiv2_location_geolocate", methods={"GET"})
     * @ParamConverter("location", options={"mapping": {"id": "uuid"}})
     */
    public function geolocateAction(Location $location)
    {
        $this->manager->setCoordinates($location);

        return new JsonResponse(
            $this->serializer->get(Location::class)->serialize($location)
        );
    }
}
