<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Organization;

use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\FOSRestController;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Organization\LocationType;

/**
 * @NamePrefix("api_")
 */
class LocationController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "locationManager" = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        LocationManager      $locationManager,
        ApiManager           $apiManager,
        ObjectManager        $om,
        Request              $request
    )
    {
        $this->formFactory     = $formFactory;
        $this->locationManager = $locationManager;
        $this->om              = $om;
        $this->request         = $request;
        $this->apiManager      = $apiManager;
    }


    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the location list",
     *     views = {"location"}
     * )
     */
    public function getLocationsAction()
    {
        return $this->locationManager->getByType(Location::TYPE_DEPARTMENT);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the location creation form",
     *     views = {"location"}
     * )
     */
    public function getCreateLocationFormAction()
    {
        $formType = new LocationType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\createLocationForm.html.twig', $form);
    }


    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the location edition form",
     *     views = {"location"}
     * )
     */
    public function getEditLocationFormAction(Location $location)
    {
        $formType = new LocationType();
        $formType->enableApi();
        $form = $this->createForm($formType, $location);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Creates a location",
     *     views = {"location"},
     *     input="Claroline\CoreBundle\Form\LocationType"
     * )
     */
    public function postLocationAction()
    {
        $locationType = new LocationType();
        $locationType->enableApi();
        $form = $this->formFactory->create($locationType, new Location());
        $form->submit($this->request);
        $location = null;
        $httpCode = 200;

        if ($form->isValid()) {
            $location = $form->getData();
            $location = $this->locationManager->create($location);
            $httpCode = 400;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $location
        );

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Admin\Location\createLocationForm.html.twig', $form, $options);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update a location",
     *     views = {"location"},
     *     input="Claroline\CoreBundle\Form\LocationType"
     * )
     */
    public function putLocationAction(Location $location)
    {
        $locationType = new LocationType();
        $locationType->enableApi();
        $form = $this->formFactory->create($locationType, $location);
        $form->submit($this->request);
        $httpCode = 400;

        if ($form->isValid()) {
            $location = $form->getData();
            $location = $this->locationManager->edit($location);
            $httpCode = 200;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $location
        );

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form, $options);
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a location",
     *     section="location",
     *     views = {"api"}
     * )
     * @EXT\ParamConverter("location", class="ClarolineCoreBundle:Organization\Location",)
     */
    public function deleteLocationAction(Location $location)
    {
        $this->locationManager->delete($location);

        return array('success');
    }
}
