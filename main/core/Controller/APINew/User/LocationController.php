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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Organization\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Organization\Location")
 * @Route("/location")
 */
class LocationController extends AbstractCrudController
{
    public function getName()
    {
        return 'location';
    }

    use HasUsersTrait;
    use HasGroupsTrait;
    use HasOrganizationsTrait;

    /**
     * @Route("/{id}/geolocate", name="apiv2_location_geolocate")
     * @Method("GET")
     * @ParamConverter("location", options={"mapping": {"id": "uuid"}})
     */
    public function geolocateAction(Location $location)
    {
        $this->container->get('claroline.manager.organization.location_manager')->setCoordinates($location);

        return new JsonResponse(
            $this->serializer->get('Claroline\CoreBundle\Entity\Organization\Location')->serialize($location)
        );
    }
}
