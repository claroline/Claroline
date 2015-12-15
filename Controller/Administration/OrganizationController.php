<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\LocationManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Form\LocationType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('organization_management')")
 * @EXT\Template()
 */
class OrganizationController extends Controller
{

    /**
     * @DI\InjectParams({
     *     "locationManager" = @DI\Inject("claroline.manager.location_manager"),
     *     "request"         = @DI\Inject("request")
     * })
     */
    public function __construct(
        LocationManager $locationManager,
        Request $request
    )
    {
        $this->locationManager = $locationManager;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_organization_index"
     * )
     */
	public function indexAction()
	{
		return array();
	}

    /**
     * @EXT\Route(
     *     "/department/locations/list",
     *     name="claro_admin_department_locations"
     * )
     */
    public function locationsAction()
    {
        $locations = $this->locationManager->getByType(Location::TYPE_DEPARTMENT);

        return array('locations' => $locations);
    }

    /**
     * @EXT\Route(
     *     "/department/location/create/form",
     *     name="claro_admin_location_create_form",
     *     options = {"expose"=true},
     * )
     * @EXT\Template()
     */
    public function locationFormCreateAction()
    {
        $form = $this->createForm(new LocationType());

        return array('form' => $form);
    }

    /**
     * @EXT\Route(
     *     "/department/location/submit/form",
     *     name="claro_admin_location_submit_form",
     *     options = {"expose"=true},
     * )
     * @EXT\Template()
     */
    public function locationFormSubmitAction()
    {
        $form = $this->createForm(new LocationType(), new Location());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
             $location = $form->getData();
             $this->locationManager->create($location);

             //serialize and return as json
             return new JsonResponse('success');
        }

        return array('form' => $form);
    }
}