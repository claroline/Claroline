<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Location;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Manager\LocationManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/location")
 */
class LocationController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;
    use HasOrganizationsTrait;
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var LocationManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        LocationManager $manager
    ) {
        $this->authorization = $authorization;
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
        $this->checkPermission('EDIT', $location, [], true);

        $this->manager->setCoordinates($location);

        return new JsonResponse(
            $this->serializer->get(Location::class)->serialize($location)
        );
    }
}
