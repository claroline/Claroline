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

use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Form\Organization\LocationType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @NamePrefix("api_")
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
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
    ) {
        $this->formFactory = $formFactory;
        $this->locationManager = $locationManager;
        $this->om = $om;
        $this->request = $request;
        $this->apiManager = $apiManager;
    }

    /**
     * @View(serializerGroups={"api_location"})
     */
    public function getLocationCreateFormAction()
    {
        $formType = new LocationType('clfm');
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\createLocationForm.html.twig', $form);
    }

    /**
     * @View(serializerGroups={"api_location"})
     * @Get("/location/{location}/edit/form")
     */
    public function getLocationEditFormAction(Location $location)
    {
        $formType = new LocationType('elfm');
        $formType->enableApi();
        $form = $this->createForm($formType, $location);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form);
    }

    /**
     * @View(serializerGroups={"api_location"})
     */
    public function postLocationAction()
    {
        $locationType = new LocationType('clfm');
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

        $options = [
            'http_code' => $httpCode,
            'extra_parameters' => $location,
            'serializer_group' => 'api_location',
        ];

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\createLocationForm.html.twig', $form, $options);
    }

    /**
     * @View(serializerGroups={"api_location"})
     */
    public function putLocationAction(Location $location)
    {
        $locationType = new LocationType('elfm');
        $locationType->enableApi();
        $form = $this->formFactory->create($locationType, $location);
        $form->submit($this->request);
        $httpCode = 400;

        if ($form->isValid()) {
            $location = $form->getData();
            $location = $this->locationManager->edit($location);
            $httpCode = 200;
        }

        $options = [
            'http_code' => $httpCode,
            'extra_parameters' => $location,
            'serializer_group' => 'api_location',
        ];

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form, $options);
    }
}
