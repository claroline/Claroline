<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Calendar;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\Calendar\YearManager;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Form\Calendar\YearType;
use Claroline\CoreBundle\Entity\Calendar\Year;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;

/**
 * @NamePrefix("api_")
 */
class YearController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "yearManager"   = @DI\Inject("claroline.manager.calendar.year_manager"),
     *     "request"       = @DI\Inject("request"),
     *     "apiManager"    = @DI\Inject("claroline.manager.api_manager"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        YearManager          $yearManager,
        ApiManager           $apiManager,
        ObjectManager        $om,
        Request              $request
    )
    {
        $this->formFactory     = $formFactory;
        $this->yearManager     = $yearManager;
        $this->om              = $om;
        $this->request         = $request;
        $this->apiManager      = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the year creation form",
     *     views = {"year"}
     * )
     */
    public function getCreateYearFormAction()
    {
        $formType = new YearType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Calendar\createYearForm.html.twig', 
            $form
        );
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the year list",
     *     views = {"year"}
     * )
     */
    public function getYearsAction()
    {
        return $this->yearManager->getAll();
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Creates a year",
     *     views = {"event"},
     *     input="Claroline\CoreBundle\Form\Calendar\YearType"
     * )
     */
    public function postYearAction(Organization $organization)
    {
        $yearType = new YearType();
        $yearType->enableApi();
        $form = $this->formFactory->create($yearType, new Year());
        $form->submit($this->request);
        $location = null;
        $httpCode = 200;

        if ($form->isValid()) {
            $year = $form->getData();
            $year->setOrganization($organization);
            $year = $this->locationManager->create($location);
            $httpCode = 400;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $location
        );

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Calendar\createYearForm.html.twig', $form, $options);
    }
}
